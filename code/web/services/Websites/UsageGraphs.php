<?php
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsiteIndexSetting.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebsitePage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/WebPageUsage.php';
require_once ROOT_DIR . '/sys/WebsiteIndexing/UserWebsiteUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Websites_UsageGraphs extends Admin_Admin {
	function launch() {
		global $interface;
		$stat = $_REQUEST['stat'];
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}
		$websiteName= $_REQUEST['websiteName'];
		$title = 'Websites Usage Graph: ' . $websiteName;
		$interface->assign('graphTitle', $title);
		$this->assignGraphSpecificTitle($stat);

		$websiteIndexSettingId = $this->getWebsiteIndexSettingIdBy($websiteName);
		$this->getAndSetInterfaceDataSeries($stat, $instanceName, $websiteIndexSettingId);
		$interface->assign('websiteName', $websiteName);

		$interface->assign('stat', $stat);
		$interface->assign('propName', 'exportToCSV');
		$interface->assign('showCSVExportButton', true);
		$interface->assign('section', 'Websites');
		
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

	// note that this will only handle tables with one stat (as is needed for websites usage data)
	// to see a version that handle multpile stats, see the Admin/UsageGraphs.php implementation
	public function buildCSV() {
		global $interface;
		$stat = $_REQUEST['stat'];
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}

		$websiteName= $_REQUEST['websiteName'];
		$websiteIndexSettingId = $this->getWebsiteIndexSettingIdBy($websiteName);
		$this->getAndSetInterfaceDataSeries($stat, $instanceName, $websiteIndexSettingId);
		$dataSeries = $interface->getVariable('dataSeries');

		$filename = "WebsitesUsageData_{$stat}.csv";
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: text/csv; charset=utf-8');
		header("Content-Disposition: attachment;filename={$filename}");
		$fp = fopen('php://output', 'w');

		// builds the first row of the table in the CSV - column headers: Dates, and the title of the graph
		fputcsv($fp, ['Dates', $stat]);

		// builds each subsequent data row - aka the column value
		foreach ($dataSeries as $dataSerie) {
			$data = $dataSerie['data'];
			$numRows = count($data);
			$dates = array_keys($data);

			if( empty($numRows)) {
				fputcsv($fp, ['no data found!']);
			}
			for($i = 0; $i < $numRows; $i++) {
				$date = $dates[$i];
				$value = $data[$date];
				$row = [$date, $value];
				fputcsv($fp, $row);
			}
		}
		exit();
	}

	/*
		The only unique identifier available to determine for which
		websiteIndexSetting to fetch data is the websiteIndexSetting's name as $websiteName. It is used
		here to find the Websites' id as only this exists on the websiteIndexSetting
		usage tables
	*/
	private function getWebsiteIndexSettingIdBy($websiteName) {
		$websiteIndexSetting = new WebsiteIndexSetting();
		$websiteIndexSetting->whereAdd('name = "' . $websiteName .'"');
		$websiteIndexSetting->selectAdd();
		$websiteIndexSetting->find();
		return $websiteIndexSetting->fetch()->id;
	}

	private function getAndSetInterfaceDataSeries($stat, $instanceName, $websiteIndexSettingId) {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];
		$usage = [];

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