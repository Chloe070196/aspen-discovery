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

	protected function getAndSetInterfaceDataSeries($stat, $instanceName): void {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];
		$usage = [];

		$websiteName= $_REQUEST['subSection'];
		$websiteIndexSettingId = $this->getWebsiteIndexSettingIdBy($websiteName);

		// for the graph displaying data retrieved from the user_website_usage table
		if ($stat == 'activeUsers') {
			$userUsage = new UserWebsiteUsage();
			$userUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$userUsage->instance = $instanceName;
			}
			$userUsage->whereAdd("websiteId = $websiteIndexSettingId");
			$userUsage->selectAdd();
			$userUsage->selectAdd('year');
			$userUsage->selectAdd('month');
			$userUsage->orderBy('year, month');

			$dataSeries['Active Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			$userUsage->selectAdd('COUNT(id) as numUsers');

			$userUsage->find();
			while ($userUsage->fetch()) {
				$curPeriod = "{$userUsage->month}-{$userUsage->year}";
				$columnLabels[] = $curPeriod;
				/** @noinspection PhpUndefinedFieldInspection */
				$dataSeries['Active Users']['data'][$curPeriod] = $userUsage->numUsers;
			}
		}

		// for the graph displaying data retrieved from the website_page_usage table
		if ($stat == 'pagesViewed' || $stat == 'pagesVisited' ) {
			$usage = new WebPageUsage();
            $recordInfo = new WebsitePage();
            $usage->joinAdd($recordInfo, 'INNER', 'record', 'webPageId', 'id');
			$usage->groupBy('year, month');
			if (!empty($instanceName)) {
				$usage->instance = $instanceName;
			}

			$usage->whereAdd("websiteId = $websiteIndexSettingId");
			$usage->selectAdd();
			$usage->selectAdd('year');
			$usage->selectAdd('month');
			$usage->orderBy('year, month');
			if ($stat == 'pagesViewed') {
				$dataSeries['Pages Viewed'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$usage->selectAdd('SUM(IF(timesViewedInSearch>0,1,0)) as numRecordViewed');
			}
			if ($stat == 'pagesVisited') {
				$dataSeries['Pages Visited'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$usage->selectAdd('SUM(IF(timesUsed>0,1,0)) as numRecordsUsed');
			}
			$usage->selectAdd('SUM(IF(timesUsed>0,1,0)) as numRecordsUsed');

			$usage->find();
			while ($usage->fetch()) {
				$curPeriod = "{$usage->month}-{$usage->year}";
				$columnLabels[] = $curPeriod;
				if ($stat == 'pagesViewed') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Pages Viewed']['data'][$curPeriod] = $usage->numRecordsUsed;
				}
				if ($stat == 'pagesVisited') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Pages Visited']['data'][$curPeriod] = $usage->numRecordsUsed;
				}
			}
		}

		$interface->assign('columnLabels', $columnLabels);
		$interface->assign('dataSeries', $dataSeries);
		$interface->assign('translateDataSeries', true);
		$interface->assign('translateColumnLabels', false);
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