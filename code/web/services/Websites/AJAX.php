<?php
require_once ROOT_DIR . '/JSON_Action.php';
class Websites_AJAX extends JSON_Action {
public function exportUsageData() {
		require_once ROOT_DIR . '/services/Websites/UsageGraphs.php';
		$WebsitesUsageGraph = new Websites_UsageGraphs(); 
		$WebsitesUsageGraph->buildCSV('Websites');
	}
}