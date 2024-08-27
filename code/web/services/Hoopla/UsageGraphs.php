<?php
require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/Hoopla/UserHooplaUsage.php';
require_once ROOT_DIR . '/sys/Hoopla/HooplaRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Hoopla_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('Hoopla');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#hoopla', 'Hoopla');
		$breadcrumbs[] = new Breadcrumb('/Hoopla/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graphs');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'hoopla';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View System Reports',
			'View Dashboards',
		]);
	}

	private function assignGraphSpecificTitle($stat) {
		global $interface;
		$title = $interface->getVariable('graphTitle');
		switch ($stat) {
			case 'activeUsers':
				$title .= ' - Active Users';
				break;
			case 'recordsUsed':
				$title .= ' - Records With Usage';
				break;
			case 'loans':
				$title .= ' - Loans';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}