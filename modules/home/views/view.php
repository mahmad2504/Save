<?php 
CheckSession();
SetSession();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge;chrome=1" >
	<?php include 'modules/assets/stylesheets.php';
	ModuleJsCode();
	?>
</head>
<body>
	<?php include 'modules/assets/header.php'; ?>
	<p></p>
	<div style="width:1000px; margin-left: auto; margin-right: auto; text-align:center;color:grey" class="center">
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