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

		$previousStep = $interface->getVariable('previousStep') ?? 'back';
		$currentStep = $interface->getVariable('currentStep') ?? 'start';
		$nextStep = $interface->getVariable('nextStep') ?? 'verification_1';

		$interface->assign('previousStep', $previousStep);
		$interface->assign('currentStep', $currentStep);
		$interface->assign('nextStep', $nextStep);
		$interface->assign('ilsUnsupported', false);

		$this->display('accountRenewal.tpl', 'Renew Your Account');
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



