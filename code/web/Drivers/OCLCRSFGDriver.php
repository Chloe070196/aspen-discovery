<?php
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGRequest.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCRSFGDriver {
	private $accessToken;
	private $_registryId;

	public function __construct() {
		$homeLocation = Location::getUserHomeLocation();
		$this->_registryId = $homeLocation ? $homeLocation->oclcRegistryId : "" ;
	}

	// Controllers

	public function getAccountSummary(User $user): AccountSummary {
		[
			$existingId,
			$summary,
		] = $user->getCachedAccountSummary('oclcRSFG');

		if ($summary === null || isset($_REQUEST['reload'])) {
			//Get account information from api
			require_once ROOT_DIR . '/sys/User/AccountSummary.php';
			$summary = new AccountSummary();
			$summary->userId = $user->id;
			$summary->source = 'oclcRSFG';
			$summary->resetCounters();

			$settings = new OCLCRSFGSetting();
			$homeLibrary = Library::getPatronHomeLibrary();
			$settings->whereAdd("id={$homeLibrary->oclcRSFGSettingsId}");
			if($settings->find()) {
				$settings->fetch();
			}
			$requests = $this->getRequests($user, $settings);
			$summary->numUnavailableHolds = count($requests['unavailable']);
			$summary->numAvailableHolds = count($requests['available']);
		}

		return $summary;
	}

	public function getRequestDetails(OCLCRSFGSetting $setting, string $oclcRequestId) {
		if (empty($this->_registryId)) {
			return [];
		}
		try {
			if (empty($this->accessToken)) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
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
		$requestInAspenDb = new OCLCRSFGRequest();
		try {
			$this->updateRequestInAspenDb($requestInAspenDb, $this->getRequestFromOCLCRSFGWithId($setting, $oclcRequestId));
		} catch (Exception $e) {
			global $logger;
			$logger->log("Could not fetch data from OCLC Resource Sharing For Groups: $e", Logger::LOG_ERROR);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not fetch data from OCLC Resource Sharing For Groups.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];

		}
		return $requestInAspenDb;
	}

	public function getRequests(User $patron, $setting): array {
		if (empty($this->_registryId)) {
			return [];
		}
		try {
			if (empty($this->accessToken)) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
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
		$requestsSent = $this->getAllRequestsFromAspenDbForPatron($patron->id);
		$openRequests = [];
		$processedRequests = [];
		foreach ($requestsSent as $requestInAspenDB) {
			if ($requestInAspenDB->oclcRequestId) {
				$requestInOclcRS4G = $this->getRequestFromOCLCRSFGWithId($setting, $requestInAspenDB->oclcRequestId);
			}
			if (!empty($requestInOclcRS4G)) {
				$requestInAspenDB->requestStatus = $requestInOclcRS4G['illRequest']['requestStatus'];
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

	public function submitRequest(OCLCRSFGSetting $setting, User $patron, $requestFormData): array {
		global $logger;
		if (empty($this->_registryId)) {
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

		$requestInAspenDb = new OCLCRSFGRequest();
		$this->populateNewRequest($requestInAspenDb, $requestFormData, $patron);

		if ($this->isDuplicate($setting, $patron->id, $requestInAspenDb)) {
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
		$requestInAspenDb->insert();
		try {
			$IllRequestCreated = $this->postToOCLCRSFG($setting->serviceBaseUrl, $requestInAspenDb);
			$requestInAspenDb->requestStatus = $IllRequestCreated['responses']['illRequest']['requestStatus'];
			$requestInAspenDb->oclcRequestId = $IllRequestCreated['responses']['illRequest']['requestId'];
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
		$requestInAspenDb->update();
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

	private function isDuplicate(OCLCRSFGSetting $setting, Int $patronId, OCLCRSFGRequest $requestInAspenDb): bool {
		$this->updateRequestsInAspenDbForPatron($setting, $patronId);
		$existingRequests = $this->getAllRequestsFromAspenDbForPatron($patronId);
		foreach ($existingRequests as $existingRequest) {
			if (!$existingRequest->catalogKey) {
				return false;
			}
			if (
				$requestInAspenDb->catalogKey == $existingRequest->catalogKey
				&& $existingRequest->requestStatus != "RETURNED"
				&& $existingRequest->requestStatus != "CLOSED"
			) {
				return true;
			}
		}
		return false;
	}

	public function updateRequestsInAspenDbForPatron(OCLCRSFGSetting $setting, int $patronId): void {
		if (empty($this->_registryId)) {
			global $logger;
			$logger->log("Could not Authenticate: home location has not been assigned an OCLC Registry Id", Logger::LOG_ERROR);
			throw  new Exception("This library branch is not configured to send ILL requests. Please contact your library.");
		}
		$requests = $this->getAllRequestsFromOCLCRSFGForPatron($setting, $patronId);
		foreach ($requests as $requestInAspenDB) {
			if (!empty($requestInOclcRS4G)) {
				$this->updateRequestInAspenDb($requestInAspenDB, $requestInOclcRS4G);
			}
		}
	}

	// Services - interacts with Aspen DB

	private function getAllRequestsFromAspenDbForPatron(Int $patronId): array {
		$requestsToProcess = [];
		$request = new OCLCRSFGRequest();
		$request->userId = $patronId;
		$request->find();
		while ($request->fetch()) {
			if (empty($request->vdxId) && ($request->status != 'Not found in OCLC Resource Sharing For Groups' && $request->status != 'CANCELLED')) {
				$requestsToProcess[] = clone $request;
			}
		}
		return $requestsToProcess;
	}

	private function updateRequestInAspenDb(OCLCRSFGRequest $requestInAspenDb, $request) {
		$requestInAspenDb->oclcRequestId = isset($request['illRequest']['requestId']) ? $request['illRequest']['requestId'] : ""; 
		$requestInAspenDb->requestStatus = isset($request['illRequest']['requestStatus']) ? $request['illRequest']['requestStatus'] : ""; 
		$requestInAspenDb->requestStatusDescription = isset($request['illRequest']['requestStatusDescription']) ? $request['illRequest']['requestStatusDescription'] : ""; 
		$requestInAspenDb->createdDate = isset($request['illRequest']['created']) ? $request['illRequest']['created'] : ""; 
		$requestInAspenDb->verification = isset($request['illRequest']['verification']) ? $request['illRequest']['verification'] : ""; 
		$requestInAspenDb->needed = isset($request['illRequest']['needed']) ? $request['illRequest']['needed'] : ""; 
		$requestInAspenDb->serviceType = isset($request['illRequest']['requester']['serviceType']) ? $request['illRequest']['requester']['serviceType'] : ""; 
		$requestInAspenDb->userId = isset($request['illRequest']['item']['userId']) ? $request['illRequest']['item']['userId'] : ""; 
		$requestInAspenDb->email = isset($request['illRequest']['item']['email']) ? $request['illRequest']['item']['email'] : ""; 
		$requestInAspenDb->isbn = isset($request['illRequest']['item']['isbn']) ? $request['illRequest']['item']['isbn'] : ""; 
		$requestInAspenDb->issn = isset($request['illRequest']['item']['issn']) ? $request['illRequest']['item']['issn'] : ""; 
		$requestInAspenDb->oclcNumber = isset($request['illRequest']['item']['oclcNumber']) ? $request['illRequest']['item']['oclcNumber'] : ""; 
		$requestInAspenDb->mediaType = isset($request['illRequest']['item']['mediaType']) ? $request['illRequest']['item']['mediaType'] : ""; 
		$requestInAspenDb->title = isset($request['illRequest']['item']['title']) ? $request['illRequest']['item']['title'] : ""; 
		$requestInAspenDb->author = isset($request['illRequest']['item']['author']) ? $request['illRequest']['item']['author'] : ""; 
		$requestInAspenDb->edition = isset($request['illRequest']['item']['edition']) ? $request['illRequest']['item']['edition'] : ""; 
		$requestInAspenDb->publisher = isset($request['illRequest']['item']['publisherName']) ? $request['illRequest']['item']['publisherName'] : ""; 
		$requestInAspenDb->language = isset($request['illRequest']['item']['language']) ? $request['illRequest']['item']['language'] : ""; 
		$requestInAspenDb->feeAccepted = isset($request['illRequest']) ? $request['illRequest']['feeAccepted'] : ""; 
		$requestInAspenDb->maximumFeeAmount = isset($request['illRequest']) ? $request['illRequest']['maximumFeeAmount'] : ""; 
		$requestInAspenDb->catalogKey = isset($request['illRequest']) ? $request['illRequest']['catalogKey'] : ""; 
		$requestInAspenDb->note = isset($request['illRequest']) ? $request['illRequest']['note'] : ""; 
		$requestInAspenDb->pickupLocation = isset($request['illRequest']) ? $request['illRequest']['pickupLocation'] : ""; 
		$requestInAspenDb->update();
	}

	// Services - interacts with the Resource Sharing Request API from OCLC

	private function getAllRequestsFromOCLCRSFGForPatron(OCLCRSFGSetting $setting, Int $patronId): array {
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

	private function getRequestFromOCLCRSFGWithId(OCLCRSFGSetting $setting, string $oclcRequestId): array|null {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $setting->serviceBaseUrl . "/requests" . "/" . $oclcRequestId;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlGetPage($url);
		if (!$response) {
			throw new Exception("No requests found with id $oclcRequestId");
		}
		return json_decode(json_encode(simplexml_load_string($response)), true)['responses'];
	}

	private function postToOCLCRSFG(string $serviceBaseUrl, OCLCRSFGRequest $newRequest): array {
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
			throw  new Exception($e->getMessage());
		}
		return $data;
	}

	private function setAccessToken(OCLCRSFGSetting $setting): void {
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

	private function createTemporaryHold($patronId, $request): Hold {
		$curRequest = new Hold();
		$curRequest->userId = $patronId;
		$curRequest->type = 'interlibrary_loan';
		$curRequest->isIll = true;
		$curRequest->source = 'oclcRSFG';
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

	private function formatRequestBody(OCLCRSFGRequest $newRequest): object {
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

	private function populateNewRequest(OCLCRSFGRequest $requestInAspenDb, &$requestFormData, User $patron): void {
		$requestInAspenDb->title = isset($requestFormData["title"]) ? strip_tags($requestFormData["title"]) : "";
		$requestInAspenDb->author = isset($requestFormData["author"]) ? strip_tags($requestFormData["author"]): "";
		$requestInAspenDb->publisher = isset($requestFormData["publisher"]) ? strip_tags($requestFormData["publisher"]) : "";
		$requestInAspenDb->isbn = isset($requestFormData["isbn"]) ? strip_tags($requestFormData["isbn"]) : "";
		$requestInAspenDb->issn = isset($requestFormData["issn"]) ? strip_tags($requestFormData["issn"]) : "";
		$requestInAspenDb->oclcNumber = isset($requestFormData["oclcNumber"]) ? strip_tags($requestFormData["oclcNumber"]) : "";
		if (isset($requestFormData["uniqueIdentifierKey"]) && isset($requestFormData["uniqueIdentifierValue"])) {
			$requestInAspenDb->{$requestFormData["uniqueIdentifierKey"]} = $requestFormData["uniqueIdentifierValue"];
		}
		$requestInAspenDb->feeAccepted = (isset($requestFormData['acceptFee']) && $requestFormData['acceptFee'] == 'true') ? 1 : 0;
		$requestInAspenDb->maximumFeeAmount = isset($requestFormData["maximumFeeAmount"]) ? strip_tags($requestFormData["maximumFeeAmount"]) : "";
		$requestInAspenDb->catalogKey = isset($requestFormData["catalogKey"]) ? strip_tags($requestFormData["catalogKey"]) : "";
		$requestInAspenDb->requestStatus = "NEW";
		$requestInAspenDb->note = isset($requestFormData["note"]) ? strip_tags($requestFormData["note"]) : "";
		$requestInAspenDb->oclcRequesterRegistryId = $this->_registryId;
		$requestInAspenDb->userId = $patron->id;
		$patronHomeLocation = $patron->getHomeLocation();
		$requestInAspenDb->pickupLocation = empty($patronHomeLocation->oclcRSFGLocation) ? $patronHomeLocation->code : $patronHomeLocation->oclcRSFGLocation;
	}
}
