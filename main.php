<?php

global $post;

//Actual logic is here
require_once ( CMS_ROOT. DS . 'includes/post.php');
require_once ( CMS_ROOT. DS . 'includes/shortcodes_prep.php');
require_once ( CMS_ROOT. DS . 'includes/formatting_prep.php'); 
require_once ( CMS_ROOT. DS . 'includes/classTextile.php');
require_once ( CMS_ROOT. DS . 'includes/cache.php');
require_once ( CMS_ROOT. DS . 'wp-includes/shortcodes.php');      //from wordpress
require_once ( CMS_ROOT. DS . 'wp-includes/formatting.php');      //from wordpress
require_once ( CMS_ROOT. DS . 'includes/basicfunctions.php');
require_once ( TEMPLATEPATH . 'functions.php');

function main() {
	global $post;
	
    //Get the uri string from the query
    $uri = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';

    //Make sure to decode characters including non-latin
    $uri = urldecode($uri);

    // START processing $_GET variables
    if (!USE_MOD_REWRITE && strpos($uri, '?') !== false) {
        $_GET = array(); // empty $_GET array since we're going to rebuild it
        list($uri, $get_var) = explode('?', $uri);
        $exploded_get = explode('&', $get_var);

        if (count($exploded_get)) {
            foreach ($exploded_get as $get) {
                list($key, $value) = explode('=', $get);
                $_GET[$key] = $value;
            }
        }
    } else if (!USE_MOD_REWRITE && (strpos($uri, '&') !== false || strpos($uri, '=') !== false)) {
    // We're NOT using mod_rewrite, and there's no question mark wich points to GET variables in combination with site root.
        $uri = '/';
    }
    // If we're using mod_rewrite, we should have a PAGE entry.
    if (USE_MOD_REWRITE && array_key_exists('PAGE', $_GET)) {
        $uri = $_GET['PAGE'];
        unset($_GET['PAGE']);
    } else if (USE_MOD_REWRITE)   // We're using mod_rewrite but don't have a PAGE entry, assume site root.
        $uri = '/';

    // Needed to allow for ajax calls to backend
    if (array_key_exists('AJAX', $_GET)) {
        $uri = '/'.ADMIN_DIR.$_GET['AJAX'];
        unset($_GET['AJAX']);
    }
    // END processing $_GET variables
    // remove suffix page if founded
    if (URL_SUFFIX !== '' and URL_SUFFIX !== '/')
        $uri = preg_replace('#^(.*)('.URL_SUFFIX.')$#i', "$1", $uri);

    if ($uri != null && $uri[0] != '/')
        $uri = '/'.$uri;
		
    //Get pages from uri
	if (! ($post instanceof Post) ) {
		$post = new Post();
	}
	$post->findByUri($uri);
    
	//run and generate contents
	$out =  executeTemplate($post);
	echo $out;
}

function prepareTemplate($type, $subtype){
  if (file_exists( TEMPLATEPATH . 'header-' . $subtype . '.php' )) {
    include ( TEMPLATEPATH . 'header-' . $subtype . '.php' );
  } else {
    include ( TEMPLATEPATH . 'header.php' );
  }	
}

function executeTemplate($post) { 
  global $bstype; // $bstype can be set via [layout] shortcode
  $maingrids = 12;
  $sidegrids = 4;
  //$post = $in;

  $content = $post->content();

  ob_start();

  //Header
  if (file_exists( TEMPLATEPATH . 'header-' . $bstype . '.php' )) {
    include ( TEMPLATEPATH . 'header-' . $bstype . '.php' );
  } else {
    include ( TEMPLATEPATH . 'header.php' );
  }
  
  //Navigation bar.
  if (file_exists( TEMPLATEPATH . 'navbar-' . $bstype . '.php' )) {
    include ( TEMPLATEPATH . 'navbar-' . $bstype . '.php' );
  } else {
    include ( TEMPLATEPATH . 'navbar.php' );
  }

  //Main contents. Sidebar is inside of the contents.
  if (file_exists( TEMPLATEPATH . 'content-' . $bstype . '.php' )) {
    include ( TEMPLATEPATH . 'content-' . $bstype . '.php' );
  } else {
    include ( TEMPLATEPATH . 'content.php' );
  }
  
  //Footer
  if (file_exists( TEMPLATEPATH . 'footer-' . $bstype . '.php' )) {
    include ( TEMPLATEPATH . 'footer-' . $bstype . '.php' );
  } else {
    include ( TEMPLATEPATH . 'footer.php' );
  }

  $out = ob_get_contents();
  if (function_exists('beforeOutput')) {
	  $out = beforeOutput($out);
  }

  ob_end_clean();
  
  return $out;
}



?>
