<?php

$modulepath = GetModulePath(); // Module can identify its path through this variable
$modulebase = $modulepath.'/..';// Base path of module directory 
$requestdata=GetRequestData();// Data passed in body of post request
$params = GetParams();        // parameters passed as get/post/url
$settings = GetSettings();    // Settings object 

function SetDefaultParams($inparams)
{
	$params = GetParams();
	foreach($inparams as $key=>$value)
	{
		//echo $key;
		if(!isset($params->$key))
			$params->$key=$value;
	}
}
// This function writes helping Javascript code for modules
function ModuleJsCode()
{
	global $params;
	echo '<script>';
		echo 'var params={';
		$del = '';
		foreach($params as $key=>$value)
		{
			if($key == 'data')
				continue;
			if($key == 'view')
				continue;
			if($key == 'test')
				continue;
			
			echo $del.'"'.$key.'":"'.$value.'"';
			$del = ',';
		}
		echo '};';
	echo 'var resource="';
	echo GetRouteResource();	
	echo '";';
	echo '</script>';
	
}

?>