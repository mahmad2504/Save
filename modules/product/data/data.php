<?php

$vuls = GetVulnerabilities($params->product);

foreach($vuls as $vul)
{
	$pfqn = $vul->package.'##'.$vul->version."##".$vul->product;
	//var_dump($pfqn);
	if(!isset($packages[$pfqn]))
	{
		$packages[$pfqn] = new \StdClass();
		$package = &$packages[$pfqn];
		$package->vuls =  array();
		$package->name = $vul->package;
		$package->version = $vul->version;
		$package->product = $vul->product;
		//echo "---->".$pfqn." ".$package->name." ".$package->version."<br>";
	}
	$package = &$packages[$pfqn];
	//echo "#---->".$pfqn." ".$package->name." ".$package->version."<br>";
	$package->vuls[] =  $vul;
}	
//var_dump($packages);
foreach($packages as $pfqn=>&$package)
{
	$fixed = 0;
	$weight_2 = 0;
	$ignored = 0;
	$fix = 0;
	$open = 0;
	foreach($package->vuls as $vul)
	{
		//var_dump($vul);
		//echo($vul->status)."<br>";
		if($vul->status == 'FIX')
			$fix++;
		else if($vul->status == 'IGNORE')
			$ignored++;
		else if($vul->status == 'FIXED')
			$fixed++;
		else
			$open++;
	}
	$package->open = $open;
	$package->fix = $fix;
	$package->fixed = $fixed;
	$package->ignored = $ignored;
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
?>