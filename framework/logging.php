<?php

$logfile = fopen($logfile,"a");
$time = date("Y-m-d h:i:sa");
function LogInfo($msg)
{
	global $logfile;
	global $time;
	
	$bt = debug_backtrace();
    $caller = array_shift($bt);
    $filename = $caller['file'];
    $line = $caller['line'];
	 
	fwrite($logfile,$time.":[INFO]".$msg."[".$filename.":".$line."]\r\n");
}
function LogError($msg)
{
	global $logfile;
	global $time;
	
	$bt = debug_backtrace();
    $caller = array_shift($bt);
    $filename = $caller['file'];
    $line = $caller['line'];
	 
	fwrite($logfile,$time.":[EROR]".$msg."[".$filename.":".$line."]\r\n");
}
function LogWarning($msg)
{
	global $logfile;
	global $time;
	$bt = debug_backtrace();
    $caller = array_shift($bt);
    $filename = $caller['file'];
    $line = $caller['line'];
	 
	fwrite($logfile,$time.":[WARN]".$msg."[".$filename.":".$line."]\r\n");
}

/**
 * Constructs the SSE data format and flushes that data to the client.
 *
 * @param string $id Timestamp/id of this connection.
 * @param string $msg Line of text that should be transmitted.
 */
function SendConsole($id , $msg) {
	global $params;
	if(!isset($params->console))
		return;
	echo $params->data . PHP_EOL;
	echo "id: $id" . PHP_EOL;
	echo "data: {\n";
	echo "data: \"msg\": \"$msg\", \n";
	echo "data: \"id\": $id\n";
	echo "data: }\n";
	echo PHP_EOL;
	ob_flush();
	flush();
}
?>