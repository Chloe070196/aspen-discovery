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

		$requestedStep = $_POST['currentStep'] ?? $_GET['currentStep'] ?? 'start'; // The step requested by the previous page/form
		$userAgrees = $_POST['userAgrees'] ?? '';

		$validationError = '';
		$currentWarningMessage = '';
		$currentStep = 'start'; 
	

		// Logic to determine what the 'currentStep' should be after processing the request
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if ($requestedStep === 'start') {
				// User clicked 'Continue' from the initial welcome page
				if ($totalChecks > 0) {
					$currentStep = 'verification_check_1';
				} else {
					$currentStep = 'done'; // No checks, skip directly to done
				}
			} elseif (str_starts_with($requestedStep, 'verification_check_')) {
				$currentCheckIndex = (int)str_replace('verification_check_', '', $requestedStep) - 1;

				if ($userAgrees === 'no') {
					// User clicked "No" and then "Continue"
					$currentWarningMessage = 'You chose not to proceed with this verification. Please review the information.';
					$currentStep = $requestedStep; // Stay on the current verification step
				} elseif ($userAgrees === 'yes') {
					// User clicked "Yes" and then "Continue"
					$nextCheckIndex = $currentCheckIndex + 1;
					if ($nextCheckIndex < $totalChecks) {
						$currentStep = 'verification_check_' . ($nextCheckIndex + 1);
					} else {
						$currentStep = 'done'; // All checks complete
					}
				} else {
					$validationError = 'Please select Yes or No to proceed.';
					$currentStep = $requestedStep;
				}
			} else {
				$currentStep = $requestedStep;
			}
		} else {
			$currentStep = $requestedStep;
		}

		$currentCheckIndex = 0;
		$currentVerificationCheck = null;

		if (str_starts_with($currentStep, 'verification_check_')) {
			$currentCheckIndex = (int)str_replace('verification_check_', '', $currentStep) - 1;
			if ($currentCheckIndex >= 0 && $currentCheckIndex < $totalChecks) {
				$currentVerificationCheck = $verificationChecks[$currentCheckIndex];
				$interface->assign('currentVerificationCheck', $currentVerificationCheck);
			} else {
				// Invalid index or index out of bounds after some navigation, force to 'done'
				$currentStep = 'done';
			}
		}

		// Determine navigation buttons (nextStep and previousStep)
		$nextStep = 'done';
		$previousStep = 'back';

		if ($currentStep === 'start') {
			if ($totalChecks > 0) {
				$nextStep = 'verification_check_1';
			} else {
				$nextStep = 'done';
			}
		} elseif (str_starts_with($currentStep, 'verification_check_')) {
			$displayIndex = (int)str_replace('verification_check_', '', $currentStep);
			if (($displayIndex + 1) < $totalChecks) {
				$nextStep = 'verification_check_' . ($displayIndex + 1);
			}
			if ($displayIndex > 0) {
				$previousStep = 'verification_check_' . ($displayIndex - 1);
			} else {
				$previousStep = 'start'; // First verification step, 'Back' goes to 'start'
			}
		}

		$interface->assign('previousStep', $previousStep);
		$interface->assign('currentStep', $currentStep);
		$interface->assign('nextStep', $nextStep);
		$interface->assign('selfRenewalSettings', $selfRenewalSettings);
		$interface->assign('totalVerificationChecks', $totalChecks);
		$interface->assign('currentCheckIndex', $currentCheckIndex);
		$interface->assign('ilsUnsupported', false);
		$interface->assign('confirmContactInformation', false);
		$interface->assign('validationError', $validationError);
		$interface->assign('currentWarningMessage', $currentWarningMessage);
		$interface->assign('userAgrees', $userAgrees);

		$this->display('accountRenewal.tpl', 'Renew Your Account');
	}

	
	protected function setVerificationStep(): array {
		$data = [];
		return $data;
	}

	/**
	 * Prepares contact information data for display and update.
	 *
	 * @return array An associative array of data suitable for templating.
	 */
	protected function getContactInformationData(): array {
		$data = [];

		$user = UserAccount::getLoggedInUser();
		// Ensure user's contact information is loaded
		$user->loadContactInformation();
		$data['profile'] = $user;

		// Get Library Settings from the home library of the current user-account
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
		$allowAccountLinking = true; // Default to true unless explicitly restricted
		$passwordLabel = 'Library Card Number'; // Default label

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

			// Check for fine restrictions on updates
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
	protected function getRenewalInformation(string $sessionKey, string $userIlsId): array {
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
