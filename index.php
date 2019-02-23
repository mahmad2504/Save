<?php
$app =  new StdClass();
require_once('framework/gypsy.php');
require_once('core/core.php');
require_once('functions.php');
//SetRoute('GET','/','modules/module1/index.php');
//SetRoute('POST','/','modules/module1/index.php');
//SetRoute('POST','/api/read/:vendor/:product/sync','modules/module1/index.php');
//SetRoute('POST','/api/read/:vendor/sync','modules/milestone/index.php');
//SetRoute('POST','/api/read/:vendor/','modules/module1/index.php');

//SetRoute('GET','/api/records','modules/module1/index.php');
//SetRoute('GET','/api/file','index.php');

Route();
SendResponse();
?>