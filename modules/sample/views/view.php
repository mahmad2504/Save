<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
?>
<!DOCTYPE html>
<html>
<head>
    <title>Milestone Data</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
	<link rel="stylesheet" type="text/css" href="<?php echo GetModulePath();?>/assets/css/style.css">
	<link id="icon" rel="shortcut icon" href="<?php echo GetModulePath();?>/assets/images/favicon.ico" type="image/x-icon" />
</head>
<body>
	<h1>Sample Module</h1>
	<div style="font-size:10px;text-align:center;color:grey" class="center">
		<div id="chart_div">
	</div>
</div>
	<script>
		var params={
		<?php
			PopulateParams($params);
		?>
		};
	</script>
	<script src="<?php echo $jquery;?>"></script>
	<script src="<?php echo $googlechart;?>"></script>
	<script src="<?php echo $gscript;?>"></script>
	<script src="<?php echo $modulepath;?>/assets/js/app.js"></script>
</body>