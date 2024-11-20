<?php
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsRequest.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCResourceSharingForGroupsDriver {
	private $accessToken;
	private $registryId;

	public function __construct() {
		$this->registryId = Location::getUserHomeLocation()->oclcRegistryId;
	}

	public function submitRequest(OCLCResourceSharingForGroupsSetting $setting, User $patron, $requestFormData): array|null{

		// step 1: GET AUTHENTICATION TOKEN FOR INSTITUTION
		try {
			if (empty($this->accessToken) || time() > $this->accessToken->expires) {
				// FIXME: check the WSKey expiry date against today's date before attempting to fetch a token
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			// TODO: check which file it logs to + that it does it
			global $logger;
			$logger->log("Error conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return null;
		}

		// step 2: INITIALISE AND POPULATE A REQUEST OBJECT
		$newRequest = new OCLCResourceSharingForGroupsRequest();
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
		$newRequest->oclcRequesterRegistryId = $this->registryId;

		// further populate newRequest using patron-related data
		$newRequest->userId = $patron->id;
		$patronHomeLocation = $patron->getHomeLocation();
		$pickupLocation = empty($patronHomeLocation->oclcResourceSharingForGroupsLocation) ? $patronHomeLocation->code : $patronHomeLocation->oclcResourceSharingForGroupsLocation;
		$newRequest->pickupLocation = $pickupLocation;

		// step 3: CHECK FOR DUPLICATES AGAINST ASPEN DB
		// TODO: first, update the requests statuses in Aspen DB by fetching from RS API
		// Only active requests should be considered for this duplicate check
		$existingRequests = $this->getAllRequestsFromAspenDbForPatron($patron->id);
		foreach ($existingRequests as $existingRequest) {
			if ($newRequest->catalogKey == $existingRequest->catalogKey) {
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
		}

		// step 4: ADD THE REQUEST RETURNED BY RS API TO ASPEN DB
		if (!empty($newRequest->insert())) {
			global $logger;
			$logger->log("Could not insert new request " . $newRequest->getLastError(), Logger::LOG_ERROR);
		}

		// step 5: SEND THE REQUEST TO THE API
		try {
			$response = $this->postToOCLCResourceSharingForGroups($setting->serviceBaseUrl, $newRequest);
			$IllRequestCreated = json_decode(json_encode(simplexml_load_string($response)));
			$newRequest->requestStatus = $IllRequestCreated->responses->illRequest->requestStatus;
			$newRequest->oclcRequestId = $IllRequestCreated->responses->illRequest->requestId;
			$newRequest->update();
		} catch (Exception $e) {
			global $logger;
			$logger->log("Error submitting an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
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

	public function getAccountSummary(User $user): AccountSummary {
		[
			$existingId,
			$summary,
		] = $user->getCachedAccountSummary('oclcResourceSharingForGroups');

		if ($summary === null || isset($_REQUEST['reload'])) {
			//Get account information from api
			require_once ROOT_DIR . '/sys/User/AccountSummary.php';
			$summary = new AccountSummary();
			$summary->userId = $user->id;
			$summary->source = 'oclcResourceSharingForGroups';
			$summary->resetCounters();

			$settings = new OCLCResourceSharingForGroupsSetting();
			$homeLibrary = Library::getPatronHomeLibrary();
			$settings->whereAdd("id={$homeLibrary->oclcResourceSharingForGroupsSettingsId}");
			if($settings->find()) {
				$settings->fetch();
			}
			$requests = $this->getRequests($user, $settings);
			$summary->numUnavailableHolds = count($requests['unavailable']);
			$summary->numAvailableHolds = count($requests['available']);
		}

		return $summary;
	}

	public function getRequests(User $patron, $setting): array {
		$requestsSent = $this->getAllRequestsFromAspenDbForPatron($patron->id);
		$openRequests = [];
		$processedRequests = [];
		foreach ($requestsSent as $requestInAspenDB) {
			$requestInOclcRS4G = $this->getRequestFromOCLCResourceSharingForGroupsWithId($setting, $requestInAspenDB->oclcRequestId);
			if (!empty($requestInOclcRS4G)) {
				$requestInAspenDB->requestStatus = $requestInOclcRS4G->illRequest->requestStatus;
				$requestInAspenDB->update();
				if(
					$requestInAspenDB->requestStatus == "REVIEW" ||
					$requestInAspenDB->requestStatus == "REVIEWING"
				){
					$openRequests[] = $this->createTemporaryHold($patron->id, $requestInAspenDB);
				}
				if(
					$requestInAspenDB->requestStatus == "RECEIVED" ||
					$requestInAspenDB->requestStatus == "CLOSED" 
				){
					$processedRequests[] = $this->createTemporaryHold($patron->id, $requestInAspenDB);
				}
			}
		}
		return [
			'unavailable' => $openRequests,
			'available' => $processedRequests
		];
	}

	public function cancelRequest(OCLCResourceSharingForGroupsSetting $setting, int $oclcRequestId) {
		$this->updateRequestInOCLCResourceSharingForGroups($setting, $oclcRequestId, 'CANCEL');
		return [
			'success' => 'true',
			'message' => translate([
				'text' => 'Your request was cancelled successfully',
				'isPublicFacing' => true,
			]),
		];
	}

	private function updateRequestInOCLCResourceSharingForGroups(OCLCResourceSharingForGroupsSetting $setting, int $oclcRequestId, $requestAction): object {
		try {
			if (empty($this->accessToken)) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Error conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return null;
		}

		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $setting->serviceBaseUrl . "/requests" . "/" . $oclcRequestId . "/" . $requestAction;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlGetPage($url);
		return json_decode(json_encode(simplexml_load_string($response)))->responses;
	}

	private function postToOCLCResourceSharingForGroups(string $serviceBaseUrl, OCLCResourceSharingForGroupsRequest $newRequest): string {
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
		return $response;
	}

	private function getAllRequestsFromOCLCResourceSharingForGroupsForPatron(OCLCResourceSharingForGroupsSetting $setting, Int $patronId) {
		try {
			if (empty($this->accessToken) || time() > $this->accessToken->expires) {
				// FIXME: check the WSKey expiry date against today's date before attempting to fetch a token
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			// TODO: check which file it logs to + that it does it
			global $logger;
			$logger->log("Error conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return null;
		}
	
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
		// FIXME: refactor how the "xml" response is parsed
		return json_decode(json_encode(simplexml_load_string($response)))->responses;
	}

	private function getRequestFromOCLCResourceSharingForGroupsWithId(OCLCResourceSharingForGroupsSetting $setting, Int $oclcRequestId) {
		try {
			if (empty($this->accessToken)) {
				// FIXME: check the WSKey expiry date against today's date before attempting to fetch a token
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Error conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return null;
		}

		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $setting->serviceBaseUrl . "/requests" . "/" . $oclcRequestId;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		try {
			$curl->addCustomHeaders($customHeaders, false);
			$curl->curl_connect($url);
			$response = $curl->curlGetPage($url);
			return json_decode(json_encode(simplexml_load_string($response)))->responses;
		} catch (Exception $e) {}
	}

	public function getAllRequestsFromAspenDbForPatron(Int $patronId) {
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

	private function createTemporaryHold($patronId, $request) {
		$curRequest = new Hold();
		$curRequest->userId = $patronId;
		$curRequest->type = 'interlibrary_loan';
		$curRequest->isIll = true;
		$curRequest->source = 'oclcResourceSharingForGroups';
		$curRequest->sourceId = $request->catalogKey;
		$curRequest->recordId = $request->catalogKey;
		$curRequest->title = $request->title;
		$curRequest->author = $request->author;
		$curRequest->status = $request->requestStatus;
		$curRequest->pickupLocationName = $request->pickupLocation;
		$curRequest->cancelId = $request->oclcRequestId;
		$curRequest->cancelable = false;
		if ($request->requestStatus == 'REVIEW' || $request->requestStatus == 'REVIEWING') {
			$curRequest->cancelable = true;
		}
		return $curRequest;
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
			$this->accessToken = $provider->getAccessToken('client_credentials', ['scope' => $setting->scopes . " context:" . $this->registryId]);
		} catch (IdentityProviderException $e) {
			exit($e->getMessage());
		};
	}

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
}
