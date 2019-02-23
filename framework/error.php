<?php
namespace gypsy;
function HandleError($errno, $errstr,$error_file,$error_line) 
{
	$msg = [$errno]." ".$errstr." - ".$error_file.":".$error_line;
	LogError($msg);
}
function HandleExceptions($exception) 
{
	LogError($exception->getMessage());
}
function SetupErrorHandler()
{
   error_reporting(E_ERROR);
   set_error_handler("gypsy\HandleError");
   set_exception_handler('gypsy\HandleExceptions');
}
if(isset($_GET['debug']))
{ }
//else
//	SetupErrorHandler();
?>