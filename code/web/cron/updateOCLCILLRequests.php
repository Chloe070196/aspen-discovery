<?php

require_once __DIR__ . '/../bootstrap.php';

require_once ROOT_DIR . '/Drivers/OCLCResourceSharingForGroupsDriver.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';
require_once ROOT_DIR . '/sys/LibraryLocation/Location.php';

$driver = new OCLCResourceSharingForGroupsDriver();
$patronsWithActiveIllRequests = $driver->getPatronsWithActiveOCLCIllRequests();

if (empty($patronsWithActiveIllRequests)) {
	die();
}

foreach ($patronsWithActiveIllRequests as $patron) {
	$homeLocation = $patron->getHomeLocation();
	if (!empty($homeLocation)) {
		continue;
	}
	$setting = new OCLCResourceSharingForGroupsSetting();
	$settingId = $patron->getHomeLibrary()->oclcResourceSharingForGroupsSettingsId;
	$setting->whereAdd("id=$settingId");
	if ($setting->find(true)) {
		$driver->setRegistryId($homeLocation->oclcRegistryId);
		$driver->updateRequestStatusesInAspenDbForPatron($setting, $patron->id);
	}
}

die();

