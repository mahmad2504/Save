<?php
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
?>