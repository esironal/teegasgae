<!-- Main content-->
<?php 
  $sidebar = $post->content('sidebar', true);
  if (file_exists(TEMPLATEPATH . 'sidebar.php') && $sidebar){
	$mainwidth = 'col-md-' . ($maingrids - $sidegrids);
	$sidewidth = 'col-md-' . $sidegrids;
  } else {
	$mainwidth = 'col-md-' . $maingrids;
	$sidewidth = '';
  }
?>
<div class="container"> 
 <div class="row">
  <div class="col-md-12"><?php echo $content; ?></div>
 </div>
</div>
