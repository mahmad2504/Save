<?php
require_once('iticket.php');
require_once('mongo.php');   // Mongo access class (helper functions)
require_once('vul.php');     // Vulnerability Collection Class (Extends Mongo)
require_once('nvd.php');     // Nvd Data feed (Extends Mongo)
require_once('packages.php');// Packages Class (Extends Mongo)


/// External Ticketing System
require_once('jira.php');// Jira Class (Extends Mongo)
require_once('mticket.php');//  (Extends Mongo)
require_once('tickets.php');// Collection class that can access all ticketing systems (Extends Mongo)


function GetVulnerabilities($product,$package='all')
{
	global $db;
	$vulcol = new Vuls();
	$tickets = new Tickets();
	if($package == 'all')
		$query = ['product.name' => [ '$regex' => $product, '$options' => 'i' ]];
	else
	{
		$query = ['product.name' => [ '$regex' => $product, '$options' => 'i' ],
				  'package' => [ '$regex' => $package, '$options' => 'i' ]];
	}		
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