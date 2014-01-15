<?php

defined('CACHEFILE')  or define('CACHEFILE', CACHEPATH . 'posts.txt');
defined('CACHEINDEX') or define('CACHEINDEX', CACHEPATH . 'index.json');//This file name is to be deoceded with md5

class Post {
	public  $index;
	private $post_content;
	private $post_title;
	private $post_description;
	private $post_keywords;
	private $post_date;
	private $post_name;
	private $post_tags;
	private $post_parent;
	private $start;
	private $header;
	private $basefields;
	private $cache;

	function __construct(){
		$this->start = microtime( TRUE );
		$this->basefields = array('id', 'slug', 'title', 'status', 'parent');
		$this->cache = new Cache();

		//check if there is update in google spreadsheet (just with header);
		$url = 'https://spreadsheets.google.com/feeds/cells/' . GSSKEY . '/' . GSSSHEETID . '/public/basic?range=A1%3AAZ1';
        //$data = $this->cache->get($url, 1, "file");
		$data = file_get_contents($url);
		if (preg_match('/<updated>(.*?)<\/updated>/ui', $data, $match)){
			$last = strtotime($match[1]);
		}
					
		if (!empty($data)) {
			$this->header = $this->getHeader($data);
		}
		
		if (!( $data  = $this->cache->get(CACHEINDEX, $last))){;
			$data = $this->readIndex($this->basefields);
			$this->cache->put(CACHEINDEX, json_encode($data));
			$this->index = $data;
		} else {
			$this->index = json_decode($data, true);
		}
	}
	
	function getNavBar(){
		/* Special logic for Navbar
		 * The contents is coming from Title.
		 */
		if (array_key_exists( 'navbar', $this->index ) && function_exists('do_shortcode')) {
			$ret = do_shortcode($this->index['navbar']['title']);
		} elseif (array_key_exists( 'navbar', $this->index )) {
			$ret = $this->index['navbar']['title'];
        }
		return $ret;
	}
	
	function getHeader($data){
		//get header/title
		$postheader = array();
		if (preg_match_all('/<title.*?>(\w*?)\d*?<\/title><content.*?>(.*?)<\/content>/uim', $data, $matches, PREG_SET_ORDER)){
			$count = 0;
			foreach ($matches as $match){
				$postheader['slug'][$match[1]] = $match[2];
				$postheader['id'][$match[2]] = $match[1];
				$postheader['num'][$match[1]] = $count;
				$count++;
			}
		}
		return $postheader;
	}
	
	function convertSlug2Id($in){
		return $this->header['id'][$in];
	}

	function convertSelectFields($ins){
		$fields = '';
		foreach($ins as $in){
			$fields[] = $this->convertSlug2Id($in);
		}
		$ret = implode(', ', $fields);	
		return $ret;
	}
	
	function fetchData($append=null, $enablecache=true){
		if ($append){
			$append = '&tq=' . rawurlencode($append);
		}
		$url = 'https://spreadsheets.google.com/tq?key=' . GSSKEY . '&gid=' . GSSSHEETGID . '&pub=1' . $append;
		
		if (!( $data = $this->cache->get($url)) or $enablecache == false){;
			$data = file_get_contents($url);
            //$data = $this->cache->get($url, 1, "file");
			$this->cache->put($url, $data);
		}
		//if (DEBUG) file_put_contents(CACHEPATH . DS . 'url.txt', $url . "\n" . $data . "\n----\n", FILE_APPEND);
		//fix google visualization json bug that comes without double quotation or empty data
		$data = preg_replace(
			array('/(\{"v":)([^"]*?)"?([\}\]]+,|,"[fp]":)/uim', '/(\},)(,\{"v"\:)/uim','/(\{"v":)"([^"]*?)([\}\]]+,)/uim'), 
			array('$1"$2"$3','$1{"v":""}$2','\1"\2"$3'),
			$data
		);
		if (preg_match('/("table":.*\]\})\}\)\;$/iu',$data, $match )){
			$data = json_decode("{" . $match[1] . "}");
		}
		if (empty($data)){
			//if (DEBUG) echo 'json>>'.$match[1];
			die('Invalid json data from google spreadsheet');
		}
		return $data;
	}
	
	function readIndex($fields){
		$append = 'select ' . $this->convertSelectFields($fields) . ' where ' . $this->convertSlug2Id('slug') .' != \'slug\' OPTIONS no_format';
		$data = $this->fetchData($append, false);		
		$rhs = array_flip($fields);
		foreach ($data->table->rows as $key => $cols){
			foreach ($rhs as $c => $rh){
				$index[$cols->c[1]->v][$c] = $cols->c[$rh]->v;				
			}
		}
		return $index;
	}

	function readPost($slug, $fields=null){
		$fields = empty($fields) ? $this->header['slug'] : $fields;
		$append = 'select ' . $this->convertSelectFields($fields) . ' where ' . $this->convertSlug2Id('slug') .' = \'' . $slug . '\'  LIMIT 1 OPTIONS no_format';		
		$data = $this->fetchData($append);
		$ret = $this->readListedPosts($data, $fields); 
		return $ret[$slug];
	}
	
	function readPosts($conditionTemplate, $fields, $page, $limit, $orderby){
		$ret = null;
		$slimit  = (empty($limit)) ? '' : ' limit ' . $limit;
		$soffset = ($page - 1) == 0 ? '' : ' offset ' . (($page - 1) * $limit);
		$append = sprintf($conditionTemplate . $orderby . $slimit . $soffset .' OPTIONS no_format', $this->convertSelectFields($fields));

		$jsdata = $this->fetchData($append);
		$data = $this->readListedPosts($jsdata, $fields);
		
		//coount total record
		$append = sprintf($conditionTemplate, 'count(' . $this->convertSlug2Id('slug'). ')');
		$jscount = $this->fetchData($append);
		$countdata = $this->readCount($jscount);
				
		return array('data'=>$data, 'totalcount'=>$countdata, 'totalpages'=>ceil($countdata / $limit), 'currentpage'=>$page);		
	}
	
	function readListedPosts($data, $fields){
		if (empty($data)) return null;
		$ret = null;
		$rhs = array_flip(array_values($fields));
		foreach ($data->table->rows as $cols){
			$key = $cols->c[$rhs['slug']]->v;
			foreach ($data->table->cols as $c => $rh){
				//do status check here or upon search;
				if ($rh->{'type'} == 'date') {
					$ret[$key][$rh->{'label'}] = strtotime($cols->c[$c]->f);					
				} else {
					$ret[$key][$rh->{'label'}] = $cols->c[$c]->v;
				}
			}
		}
		return $ret;
	}
	
	function readCount($data){
		if (empty($data)) return null;
		try {
			$count = $data->table->rows[0]->c[0]->v;
		} catch (Exception $e) {
			$count = 0;
		}
		return $count;
	}
		
	function getOrderBy($in){
		if (empty($in)) return null;
		$orderbys = explode(',', strtolower($in));
		$orderby = null;
		for ($i=0;$i < count($orderbys); $i++){
			$term = $orderbys[$i];
			$pair = explode(' ', trim($term));
			$field = $this->convertSlug2Id($pair[0]);
			if (empty($field)) continue;
			$text[] = $field . (($pair[1] == 'desc') ? ' desc' : '');
		}

		if (empty($text)) return null;
		return ' order by ' . implode(', ', $text);
	}
	
	function getTagArchives($tagname, $page=1, $limit=20, $orderby=null){
		if (empty($tagname)) return 'Please specify tag.';
		$template = 'select %s ' . 
			'where lower(' . $this->convertSlug2Id('tags') .') matches \'(^|.*,)\s*' . strtolower($tagname) . '\s*(,.*|$)\' and ' .
			$this->convertSlug2Id('exclude_list') . ' != true and ' .
			$this->convertSlug2Id('status') . ' = \'published\'';
		$orderby = $this->getOrderBy($orderby);
		$allfields = $this->header['slug'];
		return $this->readPosts($template, $allfields, $page, $limit, $orderby);			
	}
	
	function searchPostsByContent($searchterm, $page=1, $limit=20, $orderby=null){
		if (empty($searchterm)) return 'Please specify search term.';		
		$template = 'select %s ' . 
			'where lower(' . $this->convertSlug2Id('content') .') contains \'' . strtolower($searchterm) . '\' and ' . 
			$this->convertSlug2Id('exclude_list') . ' != true and ' .
			$this->convertSlug2Id('status') . ' = \'published\'';
		$orderby = $this->getOrderBy($orderby);
		$allfields = $this->header['slug'];
		return $this->readPosts($template, $allfields, $page, $limit, $orderby);
	}
	
	function readAllTags(){

		$append = 'select ' . $this->convertSlug2Id('tags') . ', count(' . $this->convertSlug2Id('id') . ') where ' .
			$this->convertSlug2Id('tags') . ' != \'\' and ' .
			$this->convertSlug2Id('exclude_list') . ' != true and ' .
			$this->convertSlug2Id('status') . ' = \'published\'' .
			' group by H';
		$data = $this->fetchData($append, true);
		
		if (empty($data)) return null;
		
		$ret = array();
		foreach ($data->table->rows as $row){
			$tags = explode(',', $row->c[0]->v);
			$value = intval($row->c[1]->v);
			foreach ($tags as $tag){
				if (array_key_exists($tag, $ret)){
					$ret[$tag]['count'] += $value;
				} else {
					$ret[$tag] = $this->checkTag($tag);				
					$ret[$tag]['count'] = $value;
				}
			}
		}
		ksort($ret);
		return $ret;
	}

	function executionTime(){
		return number_format(round((microtime(true) - $this->start) * 1000 / 1000,5),5);
	}
	
	function slug(){
		return $this->post_name;		
	}

	function content(){
		return $this->post_content;		
	}

	function date(){
		return $this->post_date;
	}
	
	function title(){
		return $this->post_title;
	}
	
	function tags(){
		return $this->post_tags;
	}
	
	function description(){
		return $this->post_description;
	}

	function keywords(){
		return $this->post_keywords;
	}

	function parent(){
		return $this->post_parent;
	}

	function checkTag($tag){
		$ret = null;
		$url = null;
		if (array_key_exists($tag, $this->index)){
			$title = $this->index[$tag]['title'];
			$url = URL_PUBLIC . $tag . '/';
		} else {
			$title = $tag;
		}
		return array('title'=>$title, 'url'=>$url);
	}

	function setTags($in){
		$ret = null;
		$tags = explode(',', str_replace(' ', '', strtolower( $in )));
		foreach ($tags as $tag){
			$ret[$tag] = $this->checkTag($tag);
		}
		return $ret;
	}
	
	function setTitle($in){
		$this->post_title = $in;
	}

	function setContent($in){
		$this->post_content = $in;
	}
	
	function findByUri($uri){
		if (empty($uri) || $uri=='/') {
			$slug = 'home';
		} else {
			$uri = str_replace('//', '/', $uri);
			if (substr( $uri, -1)=='/' ) {
				$uri =  substr( $uri, 0, -1);
			}		
			//check cached page and see if the page exists
			if (array_key_exists( substr( $uri, 1 ), $this->index )) {
				$slug = substr( $uri, 1);
				//
				if ($this->index[$slug]['status'] != 'published'){
					$slug = '404error';
				}
			} else {
				$slug = '404error';
			}
		}
		//var_dump($this->index );
		//echo $slug . "<hr>";		
		if ($slug == '404error') {
			header("HTTP/1.0 404 Not Found");
		}
		$data = $this->readPost($slug);		

		$this->post_name = $slug;
		$this->post_title = $data['title'];
		$this->post_description = $data['description'];
		$this->post_keywords = $data['keywords'];
		$this->post_date = $data['post_date']; //strtotime( $data['post_date'] );
		$this->post_tags = $this->setTags( $data['tags'] );
		$this->post_parent = $this->checkTag( $data['parent'] );
/*
		ob_start();
		echo str_replace(array("\r\n", "\r"), "\n", $data['content']);
		$this->post_content = ob_get_contents();

        if (function_exists('do_shortcode')){
            $this->post_content = do_shortcode( $this->post_content );
        }
		ob_end_clean();
*/
        $this->post_content = str_replace(array("\r\n", "\r"), "\n", $data['content']);
        if (function_exists('do_shortcode')){
            $this->post_content = do_shortcode( $this->post_content );
        }
	}
}


?>
