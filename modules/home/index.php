<?php
require_once "modules/assets/module.php";
//var_dump(GetRequestData());
//var_dump(GetCurrentRoute());
//var_dump(GetModulePath());

$path = GetModuleEndPoint();
require_once($path);
?>