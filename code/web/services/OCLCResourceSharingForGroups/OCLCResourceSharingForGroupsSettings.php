<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSetting.php';

class OCLCResourceSharingForGroups_OCLCResourceSharingForGroupsSettings extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCResourceSharingForGroupsSetting';
	}

	function getToolName(): string {
		return 'OCLCResourceSharingForGroupsSettings';
	}

	function getModule(): string {
		return 'OCLCResourceSharingForGroups';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCResourceSharingForGroupsSetting();
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$this->applyFilters($object);
		$object->orderBy($this->getSort());
		$object->find();
		$objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'id asc';
	}

	function getObjectStructure($context = ''): array {
		return OCLCResourceSharingForGroupsSetting::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getAdditionalObjectActions($existingObject): array {
		return [];
	}

	function getInstructions(): string {
		return '';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ill_integration', 'Interlibrary Loan');
		$breadcrumbs[] = new Breadcrumb('/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsSettings', 'OCLC Resource Sharing For Groups Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Settings',
		]);
	}

	function canBatchEdit(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Settings',
		]);
	}
}