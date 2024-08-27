<?php
require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsiteIndexSetting.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsitePage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebPageUsage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/UserWebsiteUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Websites_UsageGraphs extends Admin_AbstractUsageGraphs {

	function launch(): void {
		$this->launchGraph('Websites');
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
}