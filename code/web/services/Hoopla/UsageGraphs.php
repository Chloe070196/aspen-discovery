<?php
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Hoopla/UserHooplaUsage.php';
require_once ROOT_DIR . '/sys/Hoopla/HooplaRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Hoopla_UsageGraphs extends Admin_Admin {
	function launch() {
		global $interface;
		$title = 'Hoopla Usage Graph';
		$interface->assign('graphTitle', $title);
		$this->display('../Admin/usage-graph.tpl', $title);
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
}