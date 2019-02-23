<?php
global $db;
$vulcol = new Vuls();
$id = ConvertId($params->id);

$vulcol->Delete(['_id'=>$id]);
/*
$query = ['$and' => 
			[ 
				['cve' => $vul->cve],
				['product.menifest' => $vul->menifest],
				['package' => $vul->package]
			]
		 ];
$fields = ['status' => $vul->status,'comment' => $vul->comment];
$vulcol->Update($query,$fields);*/
SendResponse($id);
?>