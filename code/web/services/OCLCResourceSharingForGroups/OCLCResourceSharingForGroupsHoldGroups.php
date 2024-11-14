<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsHoldGroup.php';

class OCLCResourceSharingForGroups_OCLCResourceSharingForGroupsHoldGroups extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCResourceSharingForGroupsHoldGroup';
	}

	function getToolName(): string {
		return 'OCLCResourceSharingForGroupsHoldGroups';
	}

	function getModule(): string {
		return 'OCLCResourceSharingForGroups';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Hold Groups';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCResourceSharingForGroupsHoldGroup();
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
		return OCLCResourceSharingForGroupsHoldGroup::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/OCLCResourceSharingForGroups/OCLCResourceSharingForGroupsHoldGroups', 'OCLC Resource Sharing For Groups Hold Groups');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer OCLC Resource Sharing For Groups Hold Groups');
	}
}