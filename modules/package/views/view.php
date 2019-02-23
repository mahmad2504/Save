
<!DOCTYPE html>
<html>
<head>
    <title>Product Page</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/bootstrap.min.css';?>" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/font.awesome.min.css';?>" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/tabulator.min.css.map';?>" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/tabulator.min.css';?>" rel="stylesheet" />
	<link id="icon" rel="shortcut icon" href="<?php echo $modulebase.'/assets/images/favicon.ico';?>" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulebase.'/assets/css/msc-style.css';?>" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="<?php echo $modulepath.'/assets/css/style.css';?>">
	<?php
	ModuleJsCode();
	?>
</head>
<body>
	<h1>Package Triage</h1>
	<div style="width:90%; margin-left: auto; margin-right: auto;  class="center">
			<span class="switch">
				<input id="checkbox_viewall" type="checkbox" class="switch" id="switch-id">
				<label for="switch-id">View All</label>
			</span>
			<span style="float:right;margin-right:10px;">
				<i id="download" class="fa fa-download" aria-hidden="true"></i>  
			</span>
		<div id="table1">
	</div>
</div>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/jquery.min.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/bootstrap.min.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/tabulator.min.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/msc-script.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulebase.'/assets/js/gscript.js';?>"></script>
	<script type="text/javascript" src="<?php echo $modulepath.'/assets/js/app.js';?>"></script>	
</body>