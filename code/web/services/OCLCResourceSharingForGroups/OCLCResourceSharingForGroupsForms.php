<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsForm.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';

class OCLCResourceSharingForGroups_OCLCResourceSharingForGroupsForms extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCResourceSharingForGroupsForm';
	}

	function getToolName(): string {
		return 'OCLCResourceSharingForGroupsForms';
	}

	function getModule(): string {
		return 'OCLCResourceSharingForGroups';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Forms';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCResourceSharingForGroupsForm();
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
		return OCLCResourceSharingForGroupsForm::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ill_integration', 'Interlibrary Loan');
		$breadcrumbs[] = new Breadcrumb('/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsForms', 'OCLC Resource Sharing For Groups Forms');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Forms',
		]);
	}

	function canBatchEdit(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Forms',
		]);
	}
}