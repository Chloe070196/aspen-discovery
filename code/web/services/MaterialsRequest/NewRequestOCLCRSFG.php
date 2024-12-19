<?php


require_once ROOT_DIR . "/Action.php";

class MaterialsRequest_NewRequestOCLCRSFG extends Action {
	function launch() {
		global $interface;
		
		if (!UserAccount::isLoggedIn()) {
			header('Location: /MyAccount/Home?followupModule=MaterialsRequest&followupAction=NewRequestOCLCRSFG');
			exit;
		} else {
			$user = UserAccount::getActiveUserObj();
			$patronId = empty($_REQUEST['patronId']) ? $user->id : $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			$interface->assign('patronId', $patronId);

			
			require_once ROOT_DIR . '/Drivers/OCLCRSFGDriver.php';
			require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
			require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGForm.php';
			$settings = new OCLCRSFGSetting();
			$error = false;
			global $logger;
			$logger->log('HERE' . PHP_EOL, Logger::LOG_ERROR);
			$logger->log('HERE' . PHP_EOL, Logger::LOG_ERROR);
			$logger->log('HERE' . PHP_EOL, Logger::LOG_ERROR);
			$logger->log(json_encode($_REQUEST) . PHP_EOL, Logger::LOG_ERROR);
			if ($settings->find(true)) {
				if (isset($_REQUEST['submit'])) {
					$driver = new OCLCRSFGDriver();
					$results = $driver->submitRequest($settings, UserAccount::getActiveUserObj(), $_REQUEST, true);
					$logger->log(json_encode($result) . PHP_EOL, Logger::LOG_ERROR);

					if ($results['success']) {
						header('Location: /MyAccount/Holds#interlibrary_loan');
						exit;
					} else {
						$error = $results['message'];
					}
				}
				$homeLocation = Location::getDefaultLocationForUser();
				if ($homeLocation != null) {
					//Get configuration for the form.
					$form = new OCLCRSFGForm();
					$form->id = $homeLocation->OCLCRSFGFormId;
					if ($form->find(true)) {
						$interface->assign('OCLCRSFGForm', $form);
						$formFields = $form->getFormFields(null);
						$interface->assign('structure', $formFields);
						$interface->assign('saveButtonText', 'Submit Request');
						$fieldsForm = $interface->fetch('DataObjectUtil/objectEditForm.tpl');
						$interface->assign('oclcRSFGForm', $fieldsForm);
					} else {
						$error = translate([
							'text' => "Unable to find the specified form.",
							'isPublicFacing' => true,
						]);
					}
				} else {
					$error = translate([
						'text' => "Unable to determine home library to place request from.",
						'isPublicFacing' => true,
					]);
				}
			} else {
				$error = translate([
					'text' => "OCLC Resource Sharing For Groups Settings do not exist, please contact the library to make a request.",
					'isPublicFacing' => true,
				]);
			}

			$interface->assign('error', $error);

			$this->display('oclc-rsfg-request.tpl', 'Materials Request');
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'New Materials Request');
		return $breadcrumbs;
	}
}