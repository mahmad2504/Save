<?php
date_default_timezone_set("Asia/Karachi");
ini_set('xdebug.var_display_max_depth', '10');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');

$settings = json_decode(file_get_contents('settings.json'));
$logfile = $settings->logfile;


?>