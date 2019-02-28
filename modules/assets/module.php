<?php
$modulepath = GetModulePath(); // Module can identify its path through this variable
$modulebase = $modulepath.'/..';// Base path of module directory 
$requestdata=GetRequestData();// Data passed in body of post request
$params = GetParams();        // parameters passed as get/post/url
$settings = GetSettings();    // Settings object 

function PrintLinks()
{
	global $params;
	if(isset($params->version))
		$baseurl = 'this/../../..';
	else if(isset($params->package))
		$baseurl = 'this/../..';
	else
		$baseurl = 'this/..';
	
	$url = $baseurl;
	echo '<li class="breadcrumb-item active"><a href="'.$url.'">Home</a></li>';
	
	if(isset($params->product))
	{
		$url = $baseurl.'/'.$params->product;
		if($params->product == 'all')
			echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.'Products'.'</a></li>';
		else
			echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.$params->product.'</a></li>';
		if(isset($params->package))
		{
			$url = $baseurl.'/'.$params->product.'/'.$params->package;
			if($params->package == 'all')
				echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.'Packages'.'</a></li>';
			else
				echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.$params->package.'</a></li>';
			if(isset($params->version))
			{
				$url = $baseurl.'/'.$params->product.'/'.$params->package.'/'.$params->version;
				if($params->version == 'all')
					echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.'Versions'.'</a></li>';
				else
					echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.$params->version.'</a></li>';
			}
		}
		if(isset($params->status))
		{
			$url .= '?status='.$params->status;
			echo '<li class="breadcrumb-item active"><a href="'.$url.'">'.$params->status.'</a></li>';
		}
	}
}

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