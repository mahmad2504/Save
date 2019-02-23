<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
//ignore_user_abort(true);
//set_time_limit(0);
$old = ini_set('memory_limit', '2192M'); 
require "modules/vendor/PhpSpreadsheet/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;


$nvd = new Nvd();
$packages = new Packages();
$product_packages = $packages->Get();

foreach($product_packages as $package)
{
	$searchdata = '';
	if(isset($package->aliases))
	{
		$searchdata = '';
		foreach($package->aliases as $alias)
			$searchdata .= '"'.$alias.'" ';	
	}
	else
		$searchdata = '"'.$package->name.'" ';

	$projection = new Projection(['cve.CVE_data_meta.ID']);
	//var_dump($searchdata);
	$package->cves = $nvd->SearchText($searchdata,$projection);
	//var_dump($package->cves);
}
$packages->UpdateDb();
$vuls =  new Vuls();
$vuls->UpdateDb($product_packages,$nvd);

SendConsole(time(),"Syncing with Jira"); 
$jira = new Jira();
$jira->Sync();

SendConsole(time(),"Done"); ; 

?>