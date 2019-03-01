<?php
require_once('session.php');// Packages Class (Extends Mongo)
require_once('iticket.php');
require_once('mongo.php');   // Mongo access class (helper functions)
require_once('vul.php');     // Vulnerability Collection Class (Extends Mongo)
require_once('nvd.php');     // Nvd Data feed (Extends Mongo)
require_once('packages.php');// Packages Class (Extends Mongo)


/// External Ticketing System
require_once('jira.php');// Jira Class (Extends Mongo)
require_once('mticket.php');//  (Extends Mongo)
require_once('tickets.php');// Collection class that can access all ticketing systems (Extends Mongo)

function GetProuductsCount($product)
{
	$productststats =  [];
	$vuls =  GetProductVulnerabilityCount($product);
	foreach($vuls as $vul)
	{
		if(isset($productststats[$vul->product]))
			$product = $productststats[$vul->product];
		else
		{
			$product = new \StdClass();
			$product->name = $vul->product;
			$product->packagescount = 0;
			$product->openvulcount = 0;
			$product->fixvulcount = 0;
			$product->fixedvulcount = 0 ;
			$product->ignoredvulcount = 0;
			$productststats[$product->name] = $product;
		}

		$product->packagescount++;
		$product->openvulcount += $vul->open;
		$product->fixvulcount += $vul->fix;
		$product->fixedvulcount += $vul->fixed;
		$product->ignoredvulcount += $vul->ignored;
	}
	$productststats = array_values($productststats);
	return $productststats;
}
function GetProductVulnerabilityCount($product)
{
	$vuls = GetVulnerabilities($product);
	foreach($vuls as $vul)
	{
		//var_dump($vul);
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
		//$package->name = '<a href="www.google.com">'.$package->name.'</a>';
		$tabledata[] = &$package;
	}
	return $tabledata;
}
function isfloat($f) {
	return ($f == (string)(float)$f);
}

function GetVulnerabilities($product='all',$package='all',$version='all')
{
	global $db;
	$vulcol = new Vuls();
	$tickets = new Tickets();
	
	$query = [];
	if($product!='all')
		$query['product.name'] = [ '$regex' => $product, '$options' => 'i' ];
	if($package != 'all')
		$query['package'] = [ '$regex' => $package, '$options' => 'i' ];
	if($version != 'all')
	{
		if(isfloat($version)){
			$query['version'] = (float)$version;
		}
		else
		{
			$query['version'] = $version;
		}
		
	}
	
	//var_dump($query);
	/*
	if($package == 'all')
		$query = ['product.name' => [ '$regex' => $product, '$options' => 'i' ]];
	else
	{
		if($version == 'all')
		{
		$query = ['product.name' => [ '$regex' => $product, '$options' => 'i' ],
				  'package' => [ '$regex' => $package, '$options' => 'i' ]];
		}
		else
		{
			
			$query = ['product.name' => [ '$regex' => $product, '$options' => 'i' ],
					'package' => [ '$regex' => $package, '$options' => 'i' ],
					'version' => (float)$version];
		}
	}	*/

	$collection = $vulcol->GetHandle(); 
	$cursor  = $collection->find($query);
	$vuls = $cursor->toArray();

	//$vuls = $vulcol->FindRegEx('package',$params->package);
	$lastupdated = $vulcol->GetUpdateTime();
	//var_dump($vuls);
	$tabledata = array();
	foreach($vuls as $vul)
	{
		//var_dump($vul);
		if(!isset($vul->product))
			continue;
		
		$product = $vul->product;
		$vul->id =  (string)$vul['_id'];
		// find all vulnerabilities with cve field = $vul->cve and status = FIXED
		//$collection = $vulcol->GetHandle();

		//$cursor = $collection->find(["cve"=>$vul->cve,"status"=>"FIXED"],["projection" => ["_id"=>false,"package"=>true,"version"=>true,"product"=>true]]);
		//$fixedvuls = $cursor->toArray();
		//var_dump($fixedvul);
		//$vul->fixedvuls = $fixedvuls;
		
		
		//$vul->jira = 'HMIP-2009';
		$rtickets = $tickets->Get($vul->cve);
		$vul->vultickets = [];
		$vul->progress = [];
		$vul->othertickets = [];
		if(count($rtickets) > 0)
		{
			foreach($rtickets as $ticket)
			{
				$vulticket = false;
				foreach($ticket->product as $tproduct)
				{
					if($tproduct == $product->name)
					{
						$vul->vultickets[] = $ticket;
						$vul->progress[] = $ticket;
						$vulticket = 1;
						break;
					}
				}
				if(!$vulticket)
				{
					$vul->othertickets[] = $ticket;
				}
			}
			//$vul->tickets = $rtickets;
			//$vul->progress = $rtickets;
			//echo count($vul->tickets)."<br>";
		}
		
		unset($vul->product);
		$vul->product = $product->name;
		$vul->menifest = $product->menifest;
		$vul->weight = 0;
		//$vul->progress = rand(0,100);
		$vul->version_match = $vul->type->version_match;
		
		if(isset($vul->comment))
			$vul->comment = $vul->comment;
		else
			$vul->comment = '';
		unset($vul->type);
		$vul->valid = true;
		if($vul->updated != $lastupdated) // some old vulnerability which is no more valid
			$vul->valid = false;
		$tabledata[] = $vul;
	}
	return $tabledata;
}

?>