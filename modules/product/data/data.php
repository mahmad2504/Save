<?php
global $db;
$vulcol = new Vuls();
$vuls = $vulcol->GetVulByProduct($params->product);
$packages = array();
foreach($vuls as $vul)
{
	$vul->type->weight = VulWeight($vul->type);
	$pfqn = $vul->package.'##'.$vul->version."##".$vul->product->name;
	//var_dump($pfqn);
	if(!isset($packages[$pfqn]))
	{
		$packages[$pfqn] = new \StdClass();
		$package = &$packages[$pfqn];
		$package->vuls =  array();
		$package->name = $vul->package;
		$package->version = $vul->version;
		$package->product = $vul->product->name;
		//echo "---->".$pfqn." ".$package->name." ".$package->version."<br>";
	}
	$package = &$packages[$pfqn];
	//echo "#---->".$pfqn." ".$package->name." ".$package->version."<br>";
	$package->vuls[] =  $vul;
}	
//var_dump($packages);
foreach($packages as $pfqn=>&$package)
{
	$weight_1 = 0;
	$weight_2 = 0;
	
	foreach($package->vuls as $vul)
	{
		if($vul->type->weight == 1)
			$weight_1++;
		else if($vul->type->weight == 2)
			$weight_2++;
		
	}
	$package->level1_vulcount = $weight_1;
	$package->level2_vulcount = $weight_2;
	
	//var_dump($package);
	//echo $package->product." ".$package->name." ".$package->version." ".$weight_1." ".$weight_2."<br>";
}
$tabledata = array();
$i=0;
//var_dump($packages);
foreach($packages as &$package)
{
	unset($package->vuls);
	//var_dump($package);
	//$package->id = $i++;
	$package->name = '<a href="www.google.com">'.$package->name.'</a>';
	$tabledata[] = &$package;
}
SendResponse($tabledata);
function VulWeight($vultype)
{
	$weight = 0;
	if(strlen($vultype->package_match)>0)
		$weight++;
	if(strlen($vultype->version_match)>0)
		$weight++;
	return $weight;
}
?>