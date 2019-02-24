<?php
global $db;
$vulcol = new Vuls();
$tickets = new Tickets();

if($params->package == 'all')
	$query = ['product.name' => [ '$regex' => $params->product, '$options' => 'i' ]];
else
{
	$query = ['product.name' => [ '$regex' => $params->product, '$options' => 'i' ],
	          'package' => [ '$regex' => $params->package, '$options' => 'i' ]];
}

//,
//		  'package' => [ '$regex' => $params->package, '$options' => 'i' ]
//		 ];

//			 [ 'product.name' => [ '$regex' => $params->product, '$options' => 'i' ]]];
			
$collection = $vulcol->GetHandle(); 
$cursor  = $collection->find($query);
$vuls = $cursor->toArray();

//$vuls = $vulcol->FindRegEx('package',$params->package);
$lastupdated = $vulcol->GetUpdateTime();
//var_dump($vuls);
$tabledata = array();
foreach($vuls as $vul)
{
	
	
	$product = $vul->product;
	$vul->id =  (string)$vul['_id'];
	
	//$vul->jira = 'HMIP-2009';
	$rtickets = $tickets->Get($vul->cve);
	if(count($rtickets) > 0)
	{
		$vul->tickets = $rtickets;
		$vul->progress = $rtickets;
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
SendResponse($tabledata);

?>