<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
SendConsole(time(),"Syncing with Jira"); 
$jira = new Jira();
$jira->Sync();
SendConsole(time(),"Done");
?>