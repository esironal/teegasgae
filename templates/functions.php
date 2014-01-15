<?php

//----------------------------------------------
function codeFunc($atts, $content = null){
    extract(shortcode_atts(array(
        'linenums' => '1',
		'lang'=>''
    ), $atts));

	$class = 'prettyprint'; //Required for syntax highlight
	if(!$linenums == '0'){  //Do not display linenumber if 0 is specified
		$class .= ' linenums'; // Display linenumber if not equal to zero
		if($linenums > 1) { // Start from linenum if somethng is specified
			$class .= ':'. $linenums;
		}
	}
	
	if(!$lang == ''){
		$class .= ' lang-' .$lang; // speify lang 
	}
	$content = htmlspecialchars( $content, ENT_QUOTES ); // remove special chars
	//$content = str_replace( "t", " ", $content ); // replace tab
	return '<pre class="' . ($class) . '">' . trim($content) . '</pre>'; //finally pre/append pre
}
add_shortcode( 'code', 'codeFunc' );

//----------------------------------------------
function screenshotFunc ($atts, $content = null){
    extract(shortcode_atts(array(
        'url' => '',
        'class'=>'',
        'style'=>''
    ), $atts));
	//$imageUrl = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($url)) . '?w=500';
        $ret;
	$imageUrl = 'http://s.wordpress.com/mshots/v1/' . urlencode($url) . '?w=500';
	if ($imageUrl != '') {
           $class = empty($class) ? '' : ' class="' . $class . '"';
           $style = empty($style) ? '' : ' style="' . $style . '"';
           $ret=<<<EOT
<div{$class}{$style}>
<a href="{$url}" target="_blank"><img src="{$imageUrl}" alt="{$url}" /></a>
</div>
EOT;
	}
	return $ret;
}
add_shortcode('screenshot', 'screenshotFunc');

//----------------------------------------------
//Layout switcher. global $bstype is passed to main.php and it can trigger selection of template just like changing layout by posttype in wordpress.
function layoutFunc($atts, $content = null){
    global $post, $bstype;
    extract(shortcode_atts(array(
        'posttype' => '',
		'header'=>'',
		'navbar'=>'',
		'content'=>'',
		'footer'=>''
    ), $atts));
	$bstype = $posttype;
	$bsheader = $header;
	$bsnavbar = $navbar;
	$bsfooter = $footer;
/**/
}
add_shortcode('layout', 'layoutFunc');

//----------------------------------------------
function featuretteFunc($atts, $content = null){
    global $post;
    extract(shortcode_atts(array(
        'title' => '',
		'extent' => '',
		'image' => '',
        'style' => '',
		'imagepos' => 'right',
		'imagewidth'=>5,
		'width'=>12,
		'hr'=>'<hr class="featurette-divider">'

    ), $atts));

	$left =null;
	$right = null;
	$imagework = null;
	$mainwidth = $width;
	if (!empty($image)){
		$imagework = '<div class="col-md-' . $imagewidth . '"><img class="featurette-image img-responsive" src="' . $image . '" data-src="holder.js/500x500/auto" alt="Generic placeholder image"></div>';
		if ($imagepos=='right'){
			$left = '';
			$right = $imagework;
		} else {
			$left = $imagework;
			$right = '';
		}
		$mainwidth = $width - $imagewidth;
	}

    $content = do_shortcode($content);
    if ($width == 12){
		$ret =<<< EOT
		  <div class="row featurette">
			$left
			<div class="col-md-$mainwidth">
			  <h2 class="featurette-heading">$title<span class="text-muted">$extent</span></h2>
			  <p class="lead">$content</p>
			</div>
			$right
		  </div>
EOT;
	} else {
		$ret =<<< EOT2
		<div class="col-lg-$width">
			$imagework
			<h2>$title</h2>
			<p>$content</p>
		</div>
EOT2;
	}

	return $ret;
}
add_shortcode('featurette', 'featuretteFunc');

//----------------------------------------------

//Jumbotron  
function jumbotronFunc($atts, $content = null) {
    global $post;
    extract(shortcode_atts(array(
        'title' => 'Title',
		'button' => 'Button text',
		'href' => '',
		'style'=> '',
		'nextclass' => 'col-md-12',
        'style'=> ''
    ), $atts));

	if ($post->title()==''){
		$post->setTitle( $title );
	}
	
    //preparation for carousel data
    $style = !empty($style) ? ' style="' . $style . '"' : '';
    $href = !empty($href) ? '<p><a class="btn btn-primary btn-lg" href="' . $href . '">' . $button . '</a></p>' : '';
    $content = do_shortcode($content);
    $ret =<<< EOT
    </div>
  </div>
</div>
<div class="jumbotron"{$style}>
  <div class="container">
    <h1>$title</h1>
    <p>$content</p>$href
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="$nextclass">
EOT;
    return $ret;
}
add_shortcode('jumbotron', 'jumbotronFunc');

//----------------------------------------------

//Disqus  
function disqusFunc($atts, $content = null) {
    global $post;
    extract(shortcode_atts(array(
    ), $atts));
    
    if (!defined('DISQUS_SHORTNAME') or !file_exists( TEMPLATEPATH . 'disqus.php' )) return null;
    
    ob_start();
    include ( TEMPLATEPATH . 'disqus.php' );
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
}
add_shortcode('disqus', 'disqusFunc');


?>
