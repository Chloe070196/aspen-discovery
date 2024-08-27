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

	/*
		The only unique identifier available to determine for which
		websiteIndexSetting to fetch data is the websiteIndexSetting's name as $websiteName. It is used
		here to find the Websites' id as only this exists on the websiteIndexSetting
		usage tables
	*/
	private function getWebsiteIndexSettingIdBy($websiteName): int {
		$websiteIndexSetting = new WebsiteIndexSetting();
		$websiteIndexSetting->whereAdd('name = "' . $websiteName .'"');
		$websiteIndexSetting->selectAdd();
		$websiteIndexSetting->find();
		return $websiteIndexSetting->fetch()->id;
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