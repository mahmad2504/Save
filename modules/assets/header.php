	<!-- Navigation -->
<?php
	if(isset($params->noheaders))
		return;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
  <div class="container">
    <a class="navbar-brand" href="#">
          <img  width="40px" style="border-radius:10%;" src="<?php echo $modulebase.'/assets/images/logo.png';?>" alt="">
    </a>
    
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Home
                <span class="sr-only">(current)</span>
              </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contact</a>
        </li>
      </ul>
    </div>
	
	<span style="color:white;float:right;margin-right:10px;">
		Download <i id="download" class="fa fa-download fa-sm fa-border" aria-hidden="true"></i>  
	</span>
  </div>
</nav>