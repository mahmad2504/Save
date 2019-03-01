<?php 
session_start();
if(isset($_SESSION['noheaders']))
{
	if($_SESSION['noheaders'] == 1)			
	   $params->noheaders = 1;
	else
	   $params->noheaders = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product Page</title>
	<?php include 'modules/assets/stylesheets.php';
	ModuleJsCode();
	?>
</head>
<body>
	<?php include 'modules/assets/header.php'; ?>
	<p></p>
	<div style="margin-bottom:20px; width:90%; margin-left: auto; margin-right: auto;  class="center">
		<nav aria-label="breadcrumb">
			<ul class="breadcrumb">
			<?php 
				PrintLinks();
			?>
			</ul>
		</nav>
		<div id="table1">
	</div>
	</div>
	<?php include $modulebase.'/assets/footer.php'; ?>
	<?php include $modulebase.'/assets/jscripts.php';?>
</body>