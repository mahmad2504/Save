<?php
require_once('iticket.php');
require_once('mongo.php');   // Mongo access class (helper functions)
require_once('vul.php');     // Vulnerability Collection Class (Extends Mongo)
require_once('nvd.php');     // Nvd Data feed (Extends Mongo)
require_once('packages.php');// Packages Class (Extends Mongo)


/// External Ticketing System
require_once('jira.php');// Jira Class (Extends Mongo)
require_once('mticket.php');//  (Extends Mongo)
require_once('tickets.php');// Collection class that can access all ticketing systems (Extends Mongo)

?>