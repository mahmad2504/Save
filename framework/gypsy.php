<?php
require_once __DIR__."/globals.php";
require_once __DIR__."/settings.php";
require_once __DIR__."/error.php";
require_once __DIR__."/logging.php";
require_once __DIR__."/http_request.php";
require_once __DIR__."/router.php";
require_once __DIR__."/utility.php";
// Sets Route
// method can be any HTTP method, like GET, PUT etc (casesensitive)
// $endpoint is the uri like  can be file or function 
function SetRoute($method,$endpoint,$param)
{
	global $router;
	$router->SetRoute($method,$endpoint,$param);
}
// Sets Default Route
// param can be file or function 
function SetDefaultRoute($param)
{
	global $router;
	$router->SetDefaultRoute($param);
}
// Executes the route as per uri
function Route()
{
	global $router;
	global $httprequest;

	$filename = $router->Execute($httprequest->method(),$httprequest->requesturi(),$httprequest->query(),$httprequest->body());
	if($filename != null)
	{
		//$modulepath =  pathinfo($filename)['dirname'];
		//$modulepath= "../../../".$modulepath;
		//var_dump($modulepath);
		if(file_exists($filename))
			require_once($filename);
		else
			LogError("Route Error::".$filename." Not Found");
	}
}
		
function GetModuleEndPoint()
{
	$params = GetParams();
	//var_dump($params);
	if(isset($params->data)||isset($params->view))
	{
		if(isset($params->data))
			$resource = 'data/'.$params->data;
		else if(isset($params->view))
			$resource = 'views/'.$params->view;
		else if(isset($params->test))
			$resource = 'tests/'.$params->test;
	}
	else
		$resource = 'views/view.php';
	if(strpos($resource,'.php') == false)
			$resource .= '.php';
	return $resource;
}
function GetRouteResource()
{
	global $currentroute;
	return end($currentroute->parts);
}
function GetCurrentRoute()
{
	global $currentroute;
	return $currentroute;
}
function GetSettings()
{
	global $settings;
	return $settings;
}
function GetModulePath()
{
	global $currentroute;
	return $currentroute->modulepath;
}
function GetParams()
{
	global $httprequest;
	global $currentroute;
	$params = $httprequest->query();
	//$params->prop =$currentroute->properties;
	foreach ($currentroute->properties as $key=>$value) 
	{
		$params->$key = $value;
		//echo "$key => $obj[$key]\n";
	}
	return $params;
}
function GetRequestData()
{
	global $httprequest;
	return $httprequest->body();	
}
//Modules Should send responses through this function
function SendResponse($data=null,$error=null)
{
	global $logfile;
	$response = new StdClass();
	if($error == null)
		$response->status = 'success';
	else
		$response->status = 'fail';
	$response->error = $error;
	$response->data = $data;
	if(($data == null) && ($error == null))
	{}
	else
		echo json_encode($response);
	fclose($logfile);
	exit();
}
//require_once($DOCUMENT_ROOT.'http/reuest.php');
?>