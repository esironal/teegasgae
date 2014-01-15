<?php

//mandatory function - called by main logic - do not remove.
function beforeOutput($in){
	$pat = array(
			'/(<!-- Main content-->)(\s+<div class="container">\s+<div class="row">\s+<div class="">\s+<\/div>\s+<\/div>\s+<\/div>)/uim',
			'/<div class="container">\s*?<div class="row">\s*?<div class="col-md-12">\s*?<\/div>\s*?<\/div>\s*?<\/div>/uim',
			'/ class=["\']{2}/uim'
			);
	$rep = array('$1','');
	/*
	if (ord($out{0}) == 0xef && ord($out{1}) == 0xbb && ord($out{2}) == 0xbf) {
		$out = substr($out, 3);
	}
	*/
	return preg_replace($pat, $rep, $in);
}

function navFunc($atts, $content = null){
	global $post;
    extract(shortcode_atts(array(
		'position'=>'left',
		'title'=>'',
		'items'=>''
    ), $atts));
    $is = explode(',',$items);
    	
	$cont = null;
    foreach($is as $i){
		if (array_key_exists($i, $post->index)){
			$cont .= sprintf('<li><a href="%s/">%s</a></li>',
				URL_PUBLIC . $i,
				$post->index[$i]['title']
			);
		} else {
			$cont .= sprintf('<li>%s</li>',
				$i
			);
		}
	}
	
    if (count($is) > 1 && !empty($title)){
		$ret = '<ul class="nav navbar-nav navbar-' . $position .'">'.
			'<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $title . '<b class="caret"></b></a>'.
			'<ul class="dropdown-menu">' . $cont .'</ul></li>'.
			'</ul>';		
	} else {
		$ret = '<ul class="nav navbar-nav navbar-' . $position .'">'. $cont . '</ul>';		
	}
    
	return $ret;
}
add_shortcode( 'nav', 'navFunc' );

function tagsFunc($atts, $content = null){
	global $post;

    extract(shortcode_atts(array(
		'exclude'=>''
    ), $atts));
    
    $ret = null;
    $tags = $post->tags();
    $excludes = explode(',', str_replace(' ', '', strtolower( $exclude )));
    foreach ($tags as $tag){
		if (in_array($tag, $excludes)) continue;
		$ret .= empty($tag['url']) ? '<span class="label label-default"><i class="fa fa-tag"></i>' . $tag['title'] . '</span>&nbsp;' : '<span class="label label-success"><a href="' . $tag['url'] . '"><i class="fa fa-tag"></i>' . $tag['title'] . '</a></span>&nbsp;';
	}

	$ret = empty($ret) ? '' : '<span>Tags ' . $ret . '</span>';
    return $ret;
}
add_shortcode( 'tags', 'tagsFunc' );
   
function blogFunc($atts, $content = null){
	global $post;

    extract(shortcode_atts(array(
		'title'=>$post->title(),
		'limit'=>20,
		'thumbnail'=>''
    ), $atts));
    
    
	$ret = '<article>'.
		'<h2>' . $title . '</h2>'.
		'<hr>' .
		do_shortcode( $content ) .
		'<hr>' .
		'<div><span>Posted on ' . date('m/d/Y', $post->date() ) . '</span></div>'.
		'<div>' . tagsFunc(null) . '</div>' .
		'</div>' .
		'</article><br>';
		
    return $ret;   
}
add_shortcode( 'blog', 'blogFunc' );

function taglistFunc($atts, $content = null){
	global $post;

    extract(shortcode_atts(array(
		'title'=>'',
		'tag'=>$post->slug(),
		'limit'=>20,
		'orderby'=>'',
		'acending'=>true,
		'thumbnail'=>''
    ), $atts));
    
    $ret=null;
    $append=null;
    $pageOffset = empty($_GET['o']) ? '1' : esc_attr( $_GET['o'] );

	$tagString = $tag;
    $result = $post->getTagArchives($tagString, $pageOffset, $limit, $orderby);

    $data = $result['data'];
    $title = ($title == 'notitle') ? '' : '<h2>Tag archives for \'<strong>' . $tagString. '</strong>\'</h2>';
    $content =  empty($content) ? '' : do_shortcode( $content );
    if (is_array($data)){
		//search result
		foreach ($data as $row){
			$ret .= sprintf(
				'<li><a href="%s/">%s</a><br><span>%s</span></li>',
				URL_PUBLIC . $row['slug'],
				$row['title'],
				empty($row['excerpt']) ? '(No excerpt given)' : $row['excerpt']
			);
		}

		//page navigation
		$ret = '<ul>' . $ret . '</ul>';
		$totalpages  = $result['totalpages'];
		$currentpage = $result['currentpage'];
		if ($totalpages > 1) {
			for($i = 1; $i <= $totalpages; $i++) {
				$append .= '<li' . ( $i == $currentpage ? ' class="active"' : '' ).'>' . 
					'<a href="?o='. $i . '">' . $i .
					' <span class="sr-only">(current)</span></a></li>';
			}
			$append = '<ul class="pagination"><li' . ( $currentpage == 1 ? ' class="disabled"' : '' ). '>' .
				'<a href="?o=1">&laquo;</a></li>' . $append .
				'<li' . ( $currentpage == $totalpages ? ' class="disabled"' : '' ) .'>'.
				'<a href="?o=' . $totalpages .'">&raquo;</a></li></ul>';						
		}
		$ret .= $append;
	}

	$ret = ( $ret == $title ) ? 'No match found.' : $title . $content . $ret;
    return $ret;

    
}
add_shortcode( 'taglist', 'taglistFunc' );
    
function searchResultFunc($atts, $content = null){
	global $post;

    extract(shortcode_atts(array(
		'title'=>'',
		'limit'=>20,
		'orderby'=>'',
		'acending'=>true,
		'items'=>''
    ), $atts));
    
    $_none = 'No match found.';
    
    if (!array_key_exists('s', $_GET)) return $_none;

    $ret = null;
    $append = null;
    $originalString = $_GET['s'];
    $searchString = esc_attr( $originalString );

    if (empty($searchString)) return $_none;
    $pageOffset = empty($_GET['o']) ? '1' : esc_attr( $_GET['o'] );
    $result = $post->searchPostsByContent($searchString, $pageOffset, $limit);
    $data = $result['data'];
    $title = ($title == 'notitle') ? '' : '<h2>Search result for \'<strong>' . $originalString. '</strong>\'</h2><br>';
    $content = empty($content) ? '' : do_shortcode( $content );

    if (is_array($data)){
		//search result
		foreach ($data as $row){
			$ret .= sprintf(
				'<li><a href="%s/">%s</a><br><span>%s</span></li>',
				URL_PUBLIC . $row['slug'],
				$row['title'],
				empty($row['excerpt']) ? '(No excerpt given)' : $row['excerpt']
			);
		}

		//page navigation
		$ret = '<ul>' . $ret . '</ul>';
		$totalpages  = $result['totalpages'];
		$currentpage = $result['currentpage'];
		if ($totalpages > 1) {
			for($i = 1; $i <= $totalpages; $i++) {
				$append .= '<li' . ( $i == $currentpage ? ' class="active"' : '' ).'>' . 
					'<a href="' .  URL_PUBLIC . 'searchresult?s=' . $originalString . '&o='. $i . '">' . $i .
					' <span class="sr-only">(current)</span></a></li>';
			}
			$append = '<ul class="pagination"><li' . ( $currentpage == 1 ? ' class="disabled"' : '' ). '>' .
				'<a href="' . URL_PUBLIC . 'searchresult?s=' . $originalString . '&o=1">&laquo;</a></li>' . $append .
				'<li' . ( $currentpage == $totalpages ? ' class="disabled"' : '' ) .'>'.
				'<a href="' . URL_PUBLIC . 'searchresult?s=' . $originalString . '&o=' . $totalpages .'">&raquo;</a></li></ul>';						
		}
		$ret .= $append;
	}

	$ret = ( $ret == $title ) ? $_none : $title . $content . $ret;
    return $ret;
    
}
add_shortcode( 'searchresult', 'searchresultFunc' );

function textileFunc($atts, $content = null){
	global $post;
    extract(shortcode_atts(array(
        'option' => ''
    ), $atts));
        
   $textile = new TextileFilter();
   $content = do_shortcode(str_replace('\\n',"\n", $content));

   $ret = $textile->TextileThis($content);
   return $ret;
}
add_shortcode('textile', 'textileFunc');


function listTagsFunc($atts, $content = null){
	global $post;
    extract(shortcode_atts(array(
        'exclude' => ''
    ), $atts));
	$ret;
   
	$ret = '<ul>';
	$tags = $post->readAllTags();
    $excludes = explode(',', str_replace(' ', '', strtolower( $exclude )));
    foreach ($tags as $tag){
		if (in_array($tag, $excludes)) continue;
		$ret .= empty($tag['url']) ? '<li>' . $tag['title'] . ' (' . $tag['count'] . ')</li>' : '<li><a href="' . $tag['url'] . '">' . $tag['title'] . ' (' . $tag['count'] . ')</a></li>';
	}
	$ret .= '</ul>';
	
	return $ret;
}
add_shortcode('listtags', 'listTagsFunc');
	
	
?>
