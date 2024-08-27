<?php
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsiteIndexSetting.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsitePage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebPageUsage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/UserWebsiteUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Websites_UsageGraphs extends Admin_Admin {
	function launch() {
		$websiteName= $_REQUEST['websiteName'];
		$title = 'Websites Usage Graph: ' . $websiteName;
		$interface->assign('graphTitle', $title);
		$this->assignGraphSpecificTitle($stat);
		$this->display('../Admin/usage-graph.tpl', $title);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#web_indexer', 'Website Indexing');
		$breadcrumbs[] = new Breadcrumb('/Websites/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graphs');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'web_indexer';
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
			case 'pagesViewed':
				$title .= ' - Pages Viewed';
				break;
			case 'pagesVisited':
				$title .= ' - Pages Visited';
				break;
			case 'activeUsers':
				$title .= ' - Active Users';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}