<?php
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsRequest.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCResourceSharingForGroupsDriver {
	private $accessToken;

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
		$newRequest->oclcRequesterRegistryId = $setting->oclcRegistryId;

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
	public function getRequests(User $patron) {
		$requestsSent = $this->getAllRequestsFromAspenDbForPatron($patron->id);
		$openRequests = $this->getOpenRequests($patron->id, $requestsSent);
		return [
			'unavailable' => $openRequests,
		];
	}
	private function getAllRequestsFromAspenDbForPatron(Int $patronId) {
		$requestsSent = [];
		$oclcResourceSharingForGroupsRequest = new OCLCResourceSharingForGroupsRequest();
		$oclcResourceSharingForGroupsRequest->whereAdd("userId=" . $patronId);
		if (!empty($oclcResourceSharingForGroupsRequest->find())) {
			$requestsSent = $oclcResourceSharingForGroupsRequest->fetchAll();
		}
		return $requestsSent;
	}


	// create a temporary hold object so that open requests can be displayed
	// necessary as we are using the 'Title on Hold' page to display them
	// modelled on the VdxDriver
	private function getOpenRequests($patronId, $requestsSent) {
		$openRequests = [];
		foreach ($requestsSent as $request) {
			$curRequest = new Hold();
			$curRequest->userId = $patronId;
			$curRequest->type = 'interlibrary_loan';
			$curRequest->isIll = true;
			$curRequest->source = 'oclcResourceSharingForGroups';
			$curRequest->sourceId = $request->catalogKey;
			$curRequest->recordId = $request->catalogKey;
			$curRequest->title = $request->title;
			$curRequest->author = $request->author;
			$curRequest->status = 'Pending';
			$curRequest->pickupLocationName = $request->pickupLocation;
			$curRequest->cancelable = false;
			$openRequests[] = $curRequest;
		}
		return $openRequests;
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
			$this->accessToken = $provider->getAccessToken('client_credentials', ['scope' => $setting->scopes]);
		} catch (IdentityProviderException $e) {
			exit($e->getMessage());
		};
	}

}
