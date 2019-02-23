<!DOCTYPE html>
<html>
<head>
    <title>Product Page</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/tabulator.min.css.map';?>" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/tabulator.min.css';?>" rel="stylesheet" />
	<link id="icon" rel="shortcut icon" href="<?php echo $modulebase.'/assets/images/favicon.ico';?>" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulepath.'/assets/css/style.css';?>">
	<?php
	ModuleJsCode();
	?>
</head>
<body>
	<h1>Sample Module</h1>
	<div style="width:1000px; margin-left: auto; margin-right: auto; font-size:10px;text-align:center;color:grey" class="center">
		<div id="table1">
	</div>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/jquery.min.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/tabulator.min.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/gscript.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulepath.'/assets/js/app.js';?>"></script>
</body>