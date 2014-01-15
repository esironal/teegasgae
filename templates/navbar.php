<div class="navbar navbar-inverse navbar-fixed-top"> 
 <div class="container">
 <div class="navbar-header">
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  <a class="navbar-brand" href="<?php echo URL_PUBLIC; ?>"><?php echo SITETITLE ?></a>
 </div>
 <div class="collapse navbar-collapse navbar-ex1-collapse">
<form class="navbar-form navbar-right" role="search" method="get" action="<?php echo URL_PUBLIC ?>searchresult/">
<div class="form-group"><input type="text" class="form-control" placeholder="Search" name="s"></div>
<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
</form>	
	<?php echo $post->getNavBar();?>
 </div><!--/.nav-collapse-->
 </div><!--/.container-->
</div><!--/.navbar-->
