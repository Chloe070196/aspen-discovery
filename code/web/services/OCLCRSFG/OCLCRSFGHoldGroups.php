<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGHoldGroup.php';

class OCLCRSFG_OCLCRSFGHoldGroups extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCRSFGHoldGroup';
	}

	function getToolName(): string {
		return 'OCLCRSFGHoldGroups';
	}

	function getModule(): string {
		return 'OCLCRSFG';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Hold Groups';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCRSFGHoldGroup();
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
		return OCLCRSFGHoldGroup::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/OCLCRSFG/OCLCRSFGHoldGroups', 'OCLC Resource Sharing For Groups Hold Groups');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer OCLC Resource Sharing For Groups Hold Groups');
	}
}