<?php

defined('CACHE_EXPIRE') or define('CACHE_EXPIRE', '600');

class Cache {
	
	public $expire;
	public $exclude;
	public $type;
    public $memcache;

	//$value must be text
			 
	function __construct($expire=CACHE_EXPIRE, $exclude=array()) {
		$this->expire  = $expire;
		$this->exclude = $exclude;
        $this->rows = array();
        $this->type = "memcache"; //Make this to false if do not want to use cache (==cloud base)
        $this->memcache = new Memcache;
	}

	function put ($key, $value) {
        if (!$this->type) return false;
		$filePath = $this->getFilePath($key);
        switch ($this->type) {
            case "file":
                file_put_contents($filePath, $value);
                return true;
                break;
            case "memcache":
                $data = array('data'=>$value, 'time'=>time());
                $this->memcache->set($filePath, $data);
                break;
        }
		return false;
	}
	
	function get($key, $comptime=null) {
        if (!$this->type) return false;
        $filePath = $this->getFilePath($key);
		if ( $filePath == null ) return false;
        switch ($this->type) {
            case "file":
                if (!file_exists($filePath)) return false;
                $time = filemtime($filePath);
                $data = file_get_contents($filePath);
                break;
            case "memcache":
                $result = $this->memcache->get($filePath);
                if (empty($result)) return false;
                $time = $result['time'];
                $data = $result['data'];
                break;
        }
        
        if ( (!empty($comptime) && $time > $comptime ) or
             ( ( $time + $this->expire)  > time()    ) ) {
            $ret = $data;
        } else {
            $ret = false;
        }
        return $ret;
	}
    
	function delete($key){
        if (!$this->type) return true;
		$filePath = $this->getFilePath($key);
        switch ($this->type) {
            case "file":
                @unlink ( $filePath );
                break;
            case "memcache":
                @$this->memcached->delete( $filepath );
                break;
        }
		return true;
	}
    
	private function getFilePath($key) {
		if (in_array($key, $this->exclude)) return null;
		return CACHEPATH . md5($key) . '.txt';
	}
}

?>
