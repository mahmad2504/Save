<?php
global $db;
$vulcol = new Vuls();
$id = ConvertId($params->id);
$vul = json_decode($requestdata);

$criteria = ['_id'=>$id];
$fields = ['status' => $vul->status,'comment' => $vul->comment];
/*['$and' => 
			[ 
				['cve' => $vul->cve],
				['product.menifest' => $vul->menifest],
				['package' => $vul->package]
			]
		 ];*/

$vulcol->Update($criteria,$fields);
SendResponse("Done");
?>