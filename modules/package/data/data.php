<?php

$vuls = GetVulnerabilities($params->product,$params->package);
SendResponse($vuls);

?>