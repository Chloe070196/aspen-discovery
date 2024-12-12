<?php
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsRequest.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCResourceSharingForGroupsDriver {
	private $accessToken;
	private $_registryId;

	public function __construct() {
		$homeLocation = Location::getUserHomeLocation();
		$this->_registryId = $homeLocation ? $homeLocation->oclcRegistryId : "" ;
	}

	// Controllers

	public function submitRequest(OCLCResourceSharingForGroupsSetting $setting, User $patron, $requestFormData): array {
		if (empty($this->_registryId)) {
			global $logger;
			$logger->log("Could not Authenticate: home location has not been assigned an OCLC Registry Id", Logger::LOG_ERROR);
			throw  new Exception("This library branch is not configured to send ILL requests. Please contact your library.");
		}

		try {
			if (empty($this->accessToken) || time() > $this->accessToken->expires) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_DEBUG);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}

		$newRequestInAspenDb = new OCLCResourceSharingForGroupsRequest();
		$this->populateNewRequest($newRequestInAspenDb, $requestFormData, $patron);

		if ($this->isDuplicate($setting, $patron->id, $newRequestInAspenDb)) {
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "This title has already been requested for you.  You may only have one active request for a title.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}
		$newRequestInAspenDb->insert();
		try {
			$IllRequestCreated = $this->postToOCLCResourceSharingForGroups($setting->serviceBaseUrl, $newRequestInAspenDb);
			$newRequestInAspenDb->requestStatus = $IllRequestCreated['responses']['illRequest']['requestStatus'];
			$newRequestInAspenDb->oclcRequestId = $IllRequestCreated['responses']['illRequest']['requestId'];
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception submitting an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}
		$newRequestInAspenDb->update();
		return [
			'title' => translate([
				'text' => 'Request Sent',
				'isPublicFacing' => true,
			]),
			'message' => translate([
				'text' => "Your request has been submitted. You can check the status of your request within your account.",
				'isPublicFacing' => true,
			]),
			'success' => true,
		];
	}

	private function isDuplicate(OCLCResourceSharingForGroupsSetting $setting, Int $patronId, OCLCResourceSharingForGroupsRequest $newRequestInAspenDb): bool {
		$this->updateRequestStatusesInAspenDbForPatron($setting, $patronId);
		$existingRequests = $this->getAllRequestsFromAspenDbForPatron($patronId);
		foreach ($existingRequests as $existingRequest) {
			if (!$existingRequest->catalogKey) {
				return false;
			}
			if (
				$newRequestInAspenDb->catalogKey == $existingRequest->catalogKey
				&& $existingRequest->requestStatus != "RETURNED"
				&& $existingRequest->requestStatus != "CLOSED"
			) {
				return true;
			}
		}
		return false;
	}

	public function updateRequestStatusesInAspenDbForPatron(OCLCResourceSharingForGroupsSetting $setting, int $patronId): void {
		if (empty($this->_registryId)) {
			global $logger;
			$logger->log("Could not Authenticate: home location has not been assigned an OCLC Registry Id", Logger::LOG_ERROR);
			throw  new Exception("This library branch is not configured to send ILL requests. Please contact your library.");
		}
		$requests = $this->getAllRequestsFromOCLCResourceSharingForGroupsForPatron($setting, $patronId);
		foreach ($requests as $requestInAspenDB) {
			if (!empty($requestInOclcRS4G)) {
				$requestInAspenDB->requestStatus = $requestInOclcRS4G['illRequest']['requestStatus'];
				$requestInAspenDB->update();
			}
		}
	}

	// Services - interacts with Aspen DB

	private function getAllRequestsFromAspenDbForPatron(Int $patronId): array {
		$requestsToProcess = [];
		$request = new OCLCResourceSharingForGroupsRequest();
		$request->userId = $patronId;
		$request->find();
		while ($request->fetch()) {
			if (empty($request->vdxId) && ($request->status != 'Not found in OCLC Resource Sharing For Groups' && $request->status != 'CANCELLED')) {
				$requestsToProcess[] = clone $request;
			}
		}
		return $requestsToProcess;
	}

	// Services - interacts with the Resource Sharing Request API from OCLC

	private function getAllRequestsFromOCLCResourceSharingForGroupsForPatron(OCLCResourceSharingForGroupsSetting $setting, Int $patronId): array {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$searchTerm = "searchTerm=patronID";
		$searchValue = "searchValue=" . "$patronId";
		$url = $setting->serviceBaseUrl . "/requests" . "?" . $searchTerm . "&" . $searchValue;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlGetPage($url);
		return json_decode(json_encode(simplexml_load_string($response)), true)['responses'];
	}


	private function postToOCLCResourceSharingForGroups(string $serviceBaseUrl, OCLCResourceSharingForGroupsRequest $newRequest): array {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $serviceBaseUrl . "/requests";
		$curl = new CurlWrapper();
		$customHeaders = [
			"Content-type" => "Content-type: application/json",
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlPostBodyData($url, $this->formatRequestBody($newRequest));
		try {
			$data = json_decode(json_encode(simplexml_load_string($response)), true);
		} catch (Exception $e) {
			global $logger;
			$logger->log("HERE" . PHP_EOL, Logger::LOG_ERROR);
			throw  new Exception($e->getMessage());
		}
		return $data;
	}

	private function setAccessToken(OCLCResourceSharingForGroupsSetting $setting): void {
		require_once 'oauth2_client_php_league/autoload.php';
		$basicAuth_provider = new HttpBasicAuthOptionProvider();
		$setup_options = [
			'clientId' => $setting->clientKey,
			'clientSecret' => $setting->clientSecret,
			'urlAuthorize' => $setting->authBaseUrl . "auth", // not used for this grant type yet field still required - could set to ''
			'urlAccessToken' => $setting->authBaseUrl . "token",
			'urlResourceOwnerDetails' => '',
			'redirectUri' => '',
		];
		$provider = new GenericProvider($setup_options, ['optionProvider' => $basicAuth_provider]);
		try {
			$this->accessToken = $provider->getAccessToken('client_credentials', ['scope' => $setting->scopes . " context:" . $this->_registryId]);
			return;
		} catch (IdentityProviderException $e) {
			throw  new Exception($e->getMessage());
		};
	}

	// Helpers

	private function formatRequestBody(OCLCResourceSharingForGroupsRequest $newRequest): object {
		$illRequest = [];
		$illRequest["requestStatus"] = "PROFILING";
		$illRequest["requester"] = [
			"institution" => [
				"institutionId" => $newRequest->oclcRequesterRegistryId
			],
			"serviceType" => "LOAN",
		];
		$illRequest["item"] = [
			"verification" => "item discovered on Aspen Discovery"
		];
		if (!empty($newRequest->isbn)) {
			$illRequest["item"]["isbn"] = $newRequest->isbn;
		}
		if (!empty($newRequest->issn)) {
			$illRequest["item"]["issn"] = $newRequest->issn;
		}
		if (!empty($newRequest->oclcNumber)) {
			$illRequest["item"]["oclcNumber"] = $newRequest->oclcNumber;
		}
		$illRequest["patron"] = [
			"patronApproved" => true,
			"userId" => "{$newRequest->userId}"
		];
		return (object)["illRequest" => $illRequest];
	}

	private function populateNewRequest(OCLCResourceSharingForGroupsRequest $newRequest, &$requestFormData, User $patron): void {
		$newRequest->datePlaced = $requestFormData["datePlaced"];
		$newRequest->title = strip_tags($requestFormData["title"]);
		$newRequest->author = strip_tags($requestFormData["author"]);
		$newRequest->publisher = strip_tags($requestFormData["publisher"]);
		$newRequest->isbn = strip_tags($requestFormData["isbn"]);
		$newRequest->issn = strip_tags($requestFormData["issn"]);
		$newRequest->oclcNumber = strip_tags($requestFormData["oclcNumber"]);
		$newRequest->{$requestFormData["uniqueIdentifierKey"]} = $requestFormData["uniqueIdentifierValue"];
		$newRequest->feeAccepted =  (isset($requestFormData['acceptFee']) && $requestFormData['acceptFee'] == 'true') ? 1 : 0;
		$newRequest->maximumFeeAmount = strip_tags($requestFormData["maximumFeeAmount"]);
		$newRequest->catalogKey = strip_tags($requestFormData["catalogKey"]);
		$newRequest->status = "NEW";
		$newRequest->note = strip_tags($requestFormData["note"]);
		$newRequest->oclcRequesterRegistryId = $this->_registryId;
		$newRequest->userId = $patron->id;
		$patronHomeLocation = $patron->getHomeLocation();
		$pickupLocation = empty($patronHomeLocation->oclcResourceSharingForGroupsLocation) ? $patronHomeLocation->code : $patronHomeLocation->oclcResourceSharingForGroupsLocation;
		$newRequest->pickupLocation = $pickupLocation;
	}
}
