<?php
function restapi($params,$data)
{
	$a = array();
	$a['params']= $params;
	$a['data'] = $data;
	SendResponse(json_encode($a));
}
?>

