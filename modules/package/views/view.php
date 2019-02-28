<!DOCTYPE html>
<html>
<head>
    <title>Vulnerability Page</title>
	<?php 
	include 'modules/assets/stylesheets.php';
	ModuleJsCode();
	?>
</head>
<body>
	<?php include 'modules/assets/header.php'; ?>
	<p>
	<div style="margin-bottom:20px; width:90%; margin-left: auto; margin-right: auto;  class="center">
		<nav aria-label="breadcrumb">
		<ul class="breadcrumb">
			<?php PrintLinks();?>
		</ul>
		</nav>	
		
			<span class="switch">
				<input id="checkbox_viewall" type="checkbox" class="switch" id="switch-id">
				<label for="switch-id">All Status</label>
			</span>
		<div id="table1">
	</div>
	</div>
	</p>
	<?php include 'modules/assets/footer.php'; ?>
	<?php include 'modules/assets/jscripts.php';?>
</body>