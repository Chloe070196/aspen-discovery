<?php
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/Hoopla/UserHooplaUsage.php';
require_once ROOT_DIR . '/sys/Hoopla/HooplaRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class Hoopla_UsageGraphs extends Admin_Admin {
	function launch() {
		global $interface;
		$stat = $_REQUEST['stat'];
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}
		$title = 'Hoopla Usage Graph';
		$interface->assign('graphTitle', $title);
		$this->assignGraphSpecificTitle($stat);

		$this->getAndSetInterfaceDataSeries($stat, $instanceName);

		$interface->assign('stat', $stat);
		$interface->assign('propName', 'exportToCSV');
		$interface->assign('showCSVExportButton', true);
		$interface->assign('section', 'Hoopla');

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

	// note that this will only handle tables with one stat (as is needed for Hoopla usage data)
	// to see a version that handle multpile stats, see the Admin/UsageGraphs.php implementation
	public function buildCSV() {
		global $interface;
		$stat = $_REQUEST['stat'];
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}

		$this->getAndSetInterfaceDataSeries($stat, $instanceName);
		$dataSeries = $interface->getVariable('dataSeries');

		$filename = "HooplaUsageData_{$stat}.csv";
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

	private function getAndSetInterfaceDataSeries($stat, $instanceName) {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];

		// for the graph displaying data retrieved from the user_website_usage table
		if ($stat == 'activeUsers') {
			$userUsage = new UserHooplaUsage();
			$userUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$userUsage->instance = $instanceName;
			}
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
		if ($stat == 'recordsUsed' || $stat == 'loans') {
			$usage = new HooplaRecordUsage();
			$usage->groupBy('year, month');
			if (!empty($instanceName)) {
				$usage->instance = $instanceName;
			}

			$usage->selectAdd();
			$usage->selectAdd('year');
			$usage->selectAdd('month');
			$usage->orderBy('year, month');
			if ($stat == 'recordsUsed') {
				$dataSeries['Records With Usage'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$usage->selectAdd('COUNT(id) as numRecordsUsed');
			}
			if ($stat == 'loans') {
				$dataSeries['Loans'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$usage->selectAdd('SUM(timesCheckedOut) as totalCheckouts');
			}

			$usage->find();
			while ($usage->fetch()) {
				$curPeriod = "{$usage->month}-{$usage->year}";
				$columnLabels[] = $curPeriod;
				if ($stat == 'recordsUsed') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Records With Usage']['data'][$curPeriod] = $usage->numRecordsUsed;
				}
				if ($stat == 'loans') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Loans']['data'][$curPeriod] = $usage->totalCheckouts;
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