<?php
require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/JSON_Action.php';

global $configArray;

class OverDrive_AJAX extends JSON_Action {

	function launch($method = null) {
		$method = $_GET['method'];
		//Backwards compatibility with old Pika calls
		switch ($method) {
			case 'CheckoutOverDriveItem':
				$method = 'checkOutTitle';
				break;
			case 'PlaceOverDriveHold':
				$method = 'placeHold';
				break;
			case 'CancelOverDriveHold':
				$method = 'cancelHold';
				break;
			case 'ReturnOverDriveItem':
				$method = 'returnCheckout';
				break;
		}
		parent::launch($method);
	}

	function placeHold() {
		global $logger;
		$logger->log("Starting OverDrive/placeHold session: " . session_id(), Logger::LOG_DEBUG);

		$overDriveId = $_REQUEST['overDriveId'];
		if (UserAccount::isLoggedIn()) {
			$user = UserAccount::getLoggedInUser();
			$logger->log("User is logged in {$user->id}", Logger::LOG_ERROR);
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				if (isset($_REQUEST['overdriveEmail'])) {
					if ($_REQUEST['overdriveEmail'] != $patron->overdriveEmail) {
						$patron->overdriveEmail = $_REQUEST['overdriveEmail'];
						$patron->update();
					}
				}
				if (isset($_REQUEST['promptForOverdriveEmail'])) {
					if ($_REQUEST['promptForOverdriveEmail'] == 1 || $_REQUEST['promptForOverdriveEmail'] == 'yes' || $_REQUEST['promptForOverdriveEmail'] == 'on') {
						$patron->promptForOverdriveEmail = 1;
					} else {
						$patron->promptForOverdriveEmail = 0;
					}
					$patron->update();
				}

				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->placeHold($patron, $overDriveId);
			} else {
				$logger->log("Logged in user {$user->id} not valid for patron {$patronId}", Logger::LOG_DEBUG);
				return [
					'result' => false,
					'message' => translate([
						'text' => 'Sorry, it looks like you don\'t have permissions to place holds for that user.',
						'isPublicFacing' => true,
					]),
				];
			}
		} else {
			$logger->log("User is not logged in", Logger::LOG_DEBUG);
			return [
				'result' => false,
				'message' => 'You must be logged in to place a hold.',
			];
		}
	}

	function renewCheckout() {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->renewCheckout($patron, $overDriveId);
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to modify checkouts for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to renew titles.',
			];
		}
	}

	function checkOutTitle() {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				$result = $driver->checkOutTitle($patron, $overDriveId);
				//$logger->log("Checkout result = $result", Logger::LOG_NOTICE);
				if ($result['success']) {
					$result['buttons'] = '<a class="btn btn-primary" href="/MyAccount/CheckedOut" role="button">' . translate([
							'text' => 'View My Check Outs',
							'isPublicFacing' => true,
						]) . '</a>';
				}
				return $result;
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to checkout titles for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to checkout an item.',
			];
		}
	}

	/** @noinspection PhpUnused */
	function returnCheckout() {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->returnCheckout($patron, $overDriveId);
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to return titles for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to return an item.',
			];
		}
	}

	/** @noinspection PhpUnused */
	function selectOverDriveDownloadFormat() {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		$formatId = $_REQUEST['formatId'];
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->selectOverDriveDownloadFormat($overDriveId, $formatId, $patron);
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to download titles for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to download a title.',
			];
		}
	}

	/** @noinspection PhpUnused */
	function getDownloadLink() {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		$formatId = $_REQUEST['formatId'];
		$isSupplement = (int)filter_var($_REQUEST['isSupplement'], FILTER_VALIDATE_BOOLEAN);
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->getDownloadLink($overDriveId, $formatId, $patron, $isSupplement);
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to download titles for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to download a title.',
			];
		}
	}

	/** @noinspection PhpUnused */
	function getHoldPrompts() {
		if (!UserAccount::isLoggedIn()) {
			return [
				'success' => false,
				'message' => 'You must be logged in to place holds, please login again.',
			];
		}
		$user = UserAccount::getLoggedInUser();
		global $interface;
		$id = $_REQUEST['id'];
		$interface->assign('overDriveId', $id);
		if ($user->overdriveEmail == 'undefined') {
			$user->overdriveEmail = '';
		}
		$promptForEmail = false;
		if (strlen($user->overdriveEmail) == 0 || $user->promptForOverdriveEmail == 1) {
			$promptForEmail = true;
		}

		$overDriveUsers = $user->getRelatedEcontentUsers('overdrive');
		$interface->assign('overDriveUsers', $overDriveUsers);
		if (count($overDriveUsers) == 1) {
			$interface->assign('patronId', reset($overDriveUsers)->id);
		}

		$interface->assign('overdriveEmail', $user->overdriveEmail);
		$interface->assign('promptForEmail', $promptForEmail);
		if (count($overDriveUsers) == 0) {
			return [
				'success' => false,
				'message' => translate([
					'text' => 'Your account is not valid for OverDrive, please contact your local library.',
					'isPublicFacing' => true,
				]),
			];
		} elseif ($promptForEmail || count($overDriveUsers) > 1) {
			$promptTitle = 'OverDrive Hold Options';
			return [
				'success' => true,
				'promptNeeded' => true,
				'promptTitle' => translate([
					'text' => $promptTitle,
					'isPublicFacing' => true,
				]),
				'prompts' => $interface->fetch('OverDrive/ajax-hold-prompt.tpl'),
				'buttons' => '<button class="btn btn-primary" type="submit" name="submit" onclick="return AspenDiscovery.OverDrive.processOverDriveHoldPrompts();">' . translate([
						'text' => 'Place Hold',
						'isPublicFacing' => true,
					]) . '</button>',
			];
		} else {
			return [
				'success' => true,
				'patronId' => reset($overDriveUsers)->id,
				'promptNeeded' => false,
				'overdriveEmail' => $user->overdriveEmail,
				'promptForOverdriveEmail' => $promptForEmail,
			];
		}
	}

	/** @noinspection PhpUnused */
	function getCheckOutPrompts() {
		if (UserAccount::isLoggedIn()) {
			$user = UserAccount::getLoggedInUser();
			global $interface;
			$id = $_REQUEST['id'];
			$interface->assign('overDriveId', $id);

			$overDriveUsers = $user->getRelatedEcontentUsers('overdrive');
			$interface->assign('overDriveUsers', $overDriveUsers);

			if (count($overDriveUsers) > 1) {
				$promptTitle = 'OverDrive Checkout Options';
				return [
					'promptNeeded' => true,
					'promptTitle' => $promptTitle,
					'prompts' => $interface->fetch('OverDrive/ajax-checkout-prompt.tpl'),
					'buttons' => '<input class="btn btn-primary" type="submit" name="submit" value="Checkout Title" onclick="return AspenDiscovery.OverDrive.processOverDriveCheckoutPrompts();">',
				];
			} elseif (count($overDriveUsers) == 1) {
				return [
					'patronId' => reset($overDriveUsers)->id,
					'promptNeeded' => false,
				];
			} else {
				// No Overdrive Account Found, give the user an error message
				global $logger;
				$logger->log('No valid Overdrive account was found to check out an Overdrive title.', Logger::LOG_ERROR);
				return [
					'promptNeeded' => true,
					'promptTitle' => 'Error',
					'prompts' => translate([
						'text' => 'Your account is not valid for OverDrive, please contact your local library.',
						'isPublicFacing' => true,
					]),
					'buttons' => '',
				];
			}
		}else {
			return [
				'promptNeeded' => true,
				'promptTitle' => translate([
					'text' => 'Error.',
					'isPublicFacing' => true,
				]),
				'prompts' => translate([
					'text' => 'Your session has expired. Please login again to checkout this title.',
					'isPublicFacing' => true,
				]),
				'buttons' => '',
			];
		}
	}

	function cancelHold(): array {
		$user = UserAccount::getLoggedInUser();
		$overDriveId = $_REQUEST['overDriveId'];
		if ($user) {
			$patronId = $_REQUEST['patronId'];
			$patron = $user->getUserReferredTo($patronId);
			if ($patron) {
				require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
				$driver = new OverDriveDriver();
				return $driver->cancelHold($patron, $overDriveId);
			} else {
				return [
					'result' => false,
					'message' => 'Sorry, it looks like you don\'t have permissions to download cancel holds for that user.',
				];
			}
		} else {
			return [
				'result' => false,
				'message' => 'You must be logged in to cancel holds.',
			];
		}
	}

	function freezeHold(): array {
		$user = UserAccount::getLoggedInUser();
		$result = [
			'success' => false,
			'message' => 'Error freezing hold.',
		];
		if (!$user) {
			$result['message'] = translate([
				'text' => 'You must be logged in to freeze a hold.  Please close this dialog and login again.',
				'isPublicFacing' => true,
			]);
		} elseif (!empty($_REQUEST['patronId'])) {
			$patronId = $_REQUEST['patronId'];
			$patronOwningHold = $user->getUserReferredTo($patronId);

			if ($patronOwningHold == false) {
				$result['message'] = translate([
					'text' => 'Sorry, you do not have access to freeze holds for the supplied user.',
					'isPublicFacing' => true,
				]);
			} else {
				if (empty($_REQUEST['overDriveId'])) {
					// We aren't getting all the expected data, so make a log entry & tell user.
					$result['message'] = translate([
						'text' => 'Information about the hold to be frozen was not provided.',
						'isPublicFacing' => true,
					]);
				} else {
					$overDriveId = $_REQUEST['overDriveId'];
					$reactivationDate = isset($_REQUEST['reactivationDate']) ? $_REQUEST['reactivationDate'] : null;
					$result = $patronOwningHold->freezeOverDriveHold($overDriveId, $reactivationDate);
					if ($result['success']) {
						$message = '<div class="alert alert-success">' . $result['message'] . '</div>';
						$result['message'] = $message;
					}

					if (!$result['success'] && is_array($result['message'])) {
						/** @var string[] $messageArray */
						$messageArray = $result['message'];
						$result['message'] = implode('; ', $messageArray);
						// Millennium Holds assumes there can be more than one item processed. Here we know only one got processed,
						// but do implode as a fallback
					}
				}
			}
		} else {
			// We aren't getting all the expected data, so make a log entry & tell user.
			global $logger;
			$logger->log('Freeze Hold, no patron Id was passed in AJAX call.', Logger::LOG_ERROR);
			$result['message'] = translate([
				'text' => 'No Patron was specified.',
				'isPublicFacing' => true,
			]);
		}

		return $result;
	}

	/** @noinspection PhpUnused */
	function getReactivationDateForm() {
		global $interface;

		$user = UserAccount::getLoggedInUser();
		$patronId = $_REQUEST['patronId'];
		$patronOwningHold = $user->getUserReferredTo($patronId);
		if ($patronOwningHold != false) {
			$interface->assign('patronId', $patronId);
			$interface->assign('overDriveId', $_REQUEST['overDriveId']);

			$title = translate([
				'text' => 'Freeze Hold',
				'isPublicFacing' => true,
			]); // language customization
			return [
				'title' => $title,
				'modalBody' => $interface->fetch("OverDrive/reactivationDate.tpl"),
				'modalButtons' => "<button class='tool btn btn-primary' id='doFreezeHoldWithReactivationDate' onclick='$(\".form\").submit(); return false;'>$title</button>",
			];
		} else {
			return [
				'success' => false,
				'message' => translate([
					'text' => 'Sorry, you do not have access to freeze holds for the supplied user.',
					'isPublicFacing' => true,
				]),
			];
		}
	}

	function thawHold(): array {
		$user = UserAccount::getLoggedInUser();
		$result = [ // set default response
			'success' => false,
			'message' => translate([
				'text' => 'Error thawing hold.',
				'isPublicFacing' => true,
			]),
		];

		if (!$user) {
			$result['message'] = translate([
				'text' => 'You must be logged in to thaw a hold.  Please close this dialog and login again.',
				'isPublicFacing' => true,
			]);
		} elseif (!empty($_REQUEST['patronId'])) {
			$patronId = $_REQUEST['patronId'];
			$patronOwningHold = $user->getUserReferredTo($patronId);

			if ($patronOwningHold == false) {
				$result['message'] = translate([
					'text' => 'Sorry, you do not have access to thaw holds for the supplied user.',
					'isPublicFacing' => true,
				]);
			} else {
				if (empty($_REQUEST['overDriveId'])) {
					$result['message'] = translate([
						'text' => 'Information about the hold to be thawed was not provided.',
						'isPublicFacing' => true,
					]);
				} else {
					$overDriveId = $_REQUEST['overDriveId'];
					$result = $patronOwningHold->thawOverDriveHold($overDriveId);
				}
			}
		} else {
			// We aren't getting all the expected data, so make a log entry & tell user.
			global $logger;
			$logger->log('Thaw Hold, no patron Id was passed in AJAX call.', Logger::LOG_ERROR);
			$result['message'] = 'No Patron was specified.';
		}

		return $result;
	}

	function getStaffView() {
		$result = [
			'success' => false,
			'message' => 'Unknown error loading staff view',
		];
		$id = $_REQUEST['id'];
		require_once ROOT_DIR . '/RecordDrivers/OverDriveRecordDriver.php';
		$recordDriver = new OverDriveRecordDriver($id);
		if ($recordDriver->isValid()) {
			global $interface;
			$interface->assign('recordDriver', $recordDriver);
			$result = [
				'success' => true,
				'staffView' => $interface->fetch($recordDriver->getStaffView()),
			];
		} else {
			$result['message'] = 'Could not find that record';
		}
		return $result;
	}

	/** @noinspection PhpUnused */
	function getPreview() {
		$result = [
			'success' => false,
			'message' => 'Unknown error loading preview',
		];
		$id = $_REQUEST['id'];
		require_once ROOT_DIR . '/Drivers/OverDriveDriver.php';
		require_once ROOT_DIR . '/RecordDrivers/OverDriveRecordDriver.php';
		$recordDriver = new OverDriveRecordDriver($id);
		if ($recordDriver->isValid()) {
			require_once ROOT_DIR . '/sys/OverDrive/OverDriveAPIProductFormats.php';
			$format = new OverDriveAPIProductFormats();
			$format->id = $_REQUEST['formatId'];
			if ($format->find(true)) {
				$result['success'] = true;
				if ($_REQUEST['sampleNumber'] == 2) {
					$result['title'] = translate([
						'text' => 'Preview ' . $format->sampleSource_2,
						'isPublicFacing' => true,
						'isAdminEnteredData' => true,
					]);
					$sampleUrl = $format->sampleUrl_2;
				} else {
					$result['title'] = translate([
						'text' => 'Preview ' . $format->sampleSource_1,
						'isPublicFacing' => true,
						'isAdminEnteredData' => true,
					]);
					$sampleUrl = $format->sampleUrl_1;
				}

				$overDriveDriver = new OverDriveDriver();
				$overDriveDriver->incrementStat('numPreviews');

				$result['modalBody'] = "<iframe src='{$sampleUrl}' class='previewFrame'></iframe>";
				$result['modalButtons'] = "<a class='tool btn btn-primary' id='viewPreviewFullSize' href='$sampleUrl' target='_blank' aria-label='".translate([
						'text' => 'View Full Screen',
						'isPublicFacing' => true,
						'inAttribute' => true,
					])." (".translate(['text' => 'opens in a new window', 'isPublicFacing' => true, 'inAttribute' => true,]).")'>" . translate([
						'text' => "View Full Screen",
						'isPublicFacing' => true,
					]) . "</a>";
			} else {
				$result['message'] = 'The specified Format was not valid';
			}
		} else {
			$result['message'] = 'The specified OverDrive Product was not valid';
		}

		return $result;
	}

	/** @noinspection PhpUnused */
	function getLargeCover() {
		global $interface;

		$id = $_REQUEST['id'];
		$interface->assign('id', $id);

		return [
			'title' => translate([
				'text' => 'Cover Image',
				'isPublicFacing' => true,
			]),
			'modalBody' => $interface->fetch("OverDrive/largeCover.tpl"),
			'modalButtons' => "",
		];
	}

	public function exportUsageData() {
		require_once ROOT_DIR . '/services/OverDrive/UsageGraphs.php';
		$overDriveUsageGraph = new OverDrive_UsageGraphs();
		$overDriveUsageGraph->buildCSV('OverDrive');
	}
}