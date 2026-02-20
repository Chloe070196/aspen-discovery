<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';

class MyAccount_AccountRenewal extends MyAccount {
	function launch(): void {
		global $interface;

		$user = UserAccount::getLoggedInUser();
		if (empty($user) || !$user) {
			$this->display('accountRenewal.tpl', 'Renew Your Account');
			$interface->assign('loggedIn', false);
			return;
		}

		$ils = $user->getILSName();
		// The present version only supports Koha, but is written in such a way that enabling support for ILS can be done as future enhancements
		if ($ils !== 'koha') {
			$this->display('accountRenewal.tpl', 'Renew Your Account');
			$interface->assign('ilsUnsupported', true);
			return;
		}

		$sessionKey = 'account_renewal_data_' . $user->id;
		$renewalInfo = $this->getRenewalInformation($sessionKey, $user->unique_ils_id);
		
		$verificationChecks = $renewalInfo['data']['verification_checks'] ?? [];
		$selfRenewalSettings = $renewalInfo['data']['self_renewal_settings'] ?? [];
		$totalChecks = count($verificationChecks);


		$currentStepName = $_POST['currentStep'] ?? $_GET['currentStep'] ?? 'start'; 
		$requestedDirection = $_POST['navigation'] ?? 'reload';

		$userAgreementResponse = $_POST['userAgrees'] ?? '';

		$validationError = '';
		$currentWarningMessage = '';

		if (str_starts_with($currentStepName, 'verification_check_')) {
			$result = $this->checkUserAgreement($userAgreementResponse);
			if (isset($result['message'])) {
				if ($userAgreementResponse === 'no') {
					$currentWarningMessage = $result['message'];
				} else {
					$validationError = $result['message'];
				}
			}
		}

		// override currentStep with the next as we prepare to relaunch
		$direction = $this->getDirection($currentStepName, $requestedDirection, $result['userAgrees'] ?? false);
		$currentStepName = $this->getNextStep($direction, $currentStepName, (int)$totalChecks);
		$currentStep = $this->getCurrentStepData($currentStepName, $verificationChecks);

		if ($currentStepName === 'verifyContactInformation') {
			$contactInfoData = $this->getContactInformationData();
			foreach ($contactInfoData as $key => $value) {
				$interface->assign($key, $value);
			}
			$interface->assign('edit', true);
		}

		$interface->assign('currentStep', $currentStep);
		$interface->assign('selfRenewalSettings', $selfRenewalSettings);
		$interface->assign('totalVerificationChecks', $totalChecks);
		$interface->assign('ilsUnsupported', false);
		$interface->assign('validationError', $validationError);
		$interface->assign('currentWarningMessage', $currentWarningMessage);
		$interface->assign('userAgrees', $result['userAgrees'] ?? '');

		$this->display('accountRenewal.tpl', 'Renew Your Account');
	}

	private function getCurrentStepData(string $currentStepName, array $verificationChecks): array {
		$data = [
			'name' => $currentStepName,
			'title' => '',
			'description' => ''
		];

		if ($currentStepName === 'start') {
			$data['title'] = 'Start';
			$data['description'] = 'Welcome to the account renewal process. Please click Continue to begin.';
		} elseif (str_starts_with($currentStepName, 'verification_check_')) {
			$data['title'] = 'Verification Questions';
			$displayIndex = (int)str_replace('verification_check_', '', $currentStepName);
			$checkIndex = $displayIndex - 1;
			if (isset($verificationChecks[$checkIndex])) {
				$data['description'] = $verificationChecks[$checkIndex]['description'] ?? '';
			}
		} elseif ($currentStepName === 'verifyContactInformation') {
			$data['title'] = 'Confirm Contact Information';
			$data['description'] = 'Please review and update your contact information as needed.';
		} elseif ($currentStepName === 'done') {
			$data['title'] = 'Request Submission.';
			$data['description'] = 'All verification steps complete. Proceed to final renewal.';
		} else {
			$data['title'] = 'Thank you!.';
			$data['description'] = 'An unexpected error occurred.';
		}

		return $data;
	}

	private function getDirection(string $currentStepName, string $requestedDirection, bool $userAgrees = false): string {
		if ($requestedDirection === 'back') {
			return 'back';
		}
		if ($requestedDirection === 'next' || $requestedDirection === 'continue') {
			if (str_starts_with($currentStepName, 'verification_check_') && !$userAgrees) {
				return 'stay';
			}
			return 'next';
		}
		return 'stay';
	}

	private function getNextStep(string $direction, string $currentStepName, int $totalChecks): string {
		if ($direction === 'stay') {
			return $currentStepName;
		}

		if ($currentStepName === 'start') {
			if ($direction === 'next') {
				return $totalChecks > 0 ? 'verification_check_1' : 'verifyContactInformation';
			}
			return 'start';
		}

		if (str_starts_with($currentStepName, 'verification_check_')) {
			$displayIndex = (int)str_replace('verification_check_', '', $currentStepName);
			if ($direction === 'next') {
				if ($displayIndex < $totalChecks) {
					return 'verification_check_' . ($displayIndex + 1);
				}
				return 'verifyContactInformation';
			}
			if ($displayIndex > 1) {
				return 'verification_check_' . ($displayIndex - 1);
			}
			return 'start';
		}

		if ($currentStepName === 'verifyContactInformation') {
			if ($direction === 'next') {
				return 'done';
			}
			return $totalChecks > 0 ? 'verification_check_' . $totalChecks : 'start';
		}

		return $currentStepName;
	}

	private function checkUserAgreement(string $userAgrees): array {
		$result = [
			'message' => 'Please select Yes or No to proceed.',
		];

		if ($userAgrees === 'yes') {
			$result['userAgrees'] = true;
			unset($result['message']);
			return $result;
		}

		if ($userAgrees === 'no') {
			$result['userAgrees'] = false;
			$result['message'] = 'You chose not to proceed with this verification. Please review the information.';
			return $result;
		} 
		
		return $result;
	}

	private function getContactInformationData(): array {
		$data = [];

		$user = UserAccount::getLoggedInUser();

		$user->loadContactInformation();
		$data['profile'] = $user;

		$patronHomeLibrary = $user->getHomeLibrary(true);

		$canUpdateContactInfo = false;
		$canUpdateAddress = false;
		$canUpdatePhoneNumber = false;
		$canUpdateWorkPhoneNumber = false;
		$showWorkPhoneInProfile = false;
		$showCellphoneInProfile = false;
		$showNoticeTypeInProfile = false;
		$allowPinReset = false;
		$showAlternateLibraryOptionsInProfile = false;
		$allowAccountLinking = true;
		$passwordLabel = 'Library Card Number';

		if ($patronHomeLibrary != null) {
			$canUpdateContactInfo = ($patronHomeLibrary->allowProfileUpdates == 1);
			$canUpdateAddress = ($patronHomeLibrary->allowPatronAddressUpdates == 1);
			$canUpdatePhoneNumber = ($patronHomeLibrary->allowPatronPhoneNumberUpdates == 1);
			$showWorkPhoneInProfile = ($patronHomeLibrary->showWorkPhoneInProfile == 1);
			$showCellphoneInProfile = ($patronHomeLibrary->showCellphoneInProfile == 1);
			$canUpdateWorkPhoneNumber = ($patronHomeLibrary->allowPatronWorkPhoneNumberUpdates == 1);
			$showNoticeTypeInProfile = ($patronHomeLibrary->showNoticeTypeInProfile == 1);
			$allowPinReset = ($patronHomeLibrary->allowPinReset == 1);
			$showAlternateLibraryOptionsInProfile = ($patronHomeLibrary->showAlternateLibraryOptionsInProfile == 1);
			$allowAccountLinking = ($patronHomeLibrary->allowLinkedAccounts == 1);
			$passwordLabel = str_replace('Your', '', $patronHomeLibrary->loginFormPasswordLabel ? $patronHomeLibrary->loginFormPasswordLabel : 'Library Card Number');

			if (($user->_finesVal > $patronHomeLibrary->maxFinesToAllowAccountUpdates) && ($patronHomeLibrary->maxFinesToAllowAccountUpdates > 0)) {
				$canUpdateContactInfo = false;
				$canUpdateAddress = false;
			}
		}

		$data['canUpdateContactInfo'] = $canUpdateContactInfo;
		$data['canUpdateAddress'] = $canUpdateAddress;
		$data['canUpdatePhoneNumber'] = $canUpdatePhoneNumber;
		$data['canUpdateWorkPhoneNumber'] = $canUpdateWorkPhoneNumber;
		$data['showWorkPhoneInProfile'] = $showWorkPhoneInProfile;
		$data['showCellphoneInProfile'] = $showCellphoneInProfile;
		$data['showNoticeTypeInProfile'] = $showNoticeTypeInProfile;
		$data['allowPinReset'] = $allowPinReset;
		$data['showAlternateLibraryOptions'] = $showAlternateLibraryOptionsInProfile;
		$data['allowAccountLinking'] = $allowAccountLinking;
		$data['passwordLabel'] = $passwordLabel;
		$data['showPreferredNameInProfile'] = $user->showPreferredNameInProfile();
		$data['allowUpdatesOfPreferredName'] = $user->allowUpdatesOfPreferredName();
		$data['pickupLocations'] = $user->getValidPickupBranches($user->getAccountProfile()->recordSource);

		return $data;
	}


	/**
	 * Gets account renewal information from the ILS driver, caching the result in the session.
	 *
	 * @return array The renewal information from the ILS driver.
	 */
	private function getRenewalInformation(string $sessionKey, string $userIlsId): array {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}

		if (isset($_SESSION[$sessionKey])) {
			return $_SESSION[$sessionKey];
		}

		$ilsDriver = CatalogFactory::getCatalogConnectionInstance();

		$renewalInfo = $ilsDriver->getAccountRenewalInformationForPatron($userIlsId);
		$_SESSION[$sessionKey] = $renewalInfo;

		return $renewalInfo;
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Renew Your Account');
		return $breadcrumbs;
	}
}
