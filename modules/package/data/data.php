<?php
$product='all';
if(isset($params->product))
	$product  = $params->product;

$version = 'all';
if(isset($params->version))
	$version  = $params->version;

$package = 'all';
if(isset($params->package))
	$package = $params->package;

$vuls = GetVulnerabilities($product,$package,$version);
SendResponse($vuls);

?>