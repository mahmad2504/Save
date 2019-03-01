<?php
function CheckSession()
{
	global $params;
	session_start();
	if(isset($params->noheaders))
	{
		if($params->noheaders >= 1)			
			$_SESSION['noheaders'] = 1;
		else
			$_SESSION['noheaders'] = 0;
	}
}
function SetSession()
{
	global $params;
	if(isset($_SESSION['noheaders']))
	{
		if($_SESSION['noheaders'] == 1)			
			$params->noheaders = 1;
		else
		$params->noheaders = 0;
	}
}
?>