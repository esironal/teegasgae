<?php

//Ohter config
define('IN_CMS', true); //For textile
define('CMS_VERSION', '0.0.1');
define('CMS_ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once(CMS_ROOT. DS .'config.php');

$changedurl = str_replace('//','|',URL_PUBLIC);
$lastslash = strpos($changedurl, '/');

if (false === $lastslash) {
    define('URI_PUBLIC', '/');
}
else {
    define('URI_PUBLIC', substr($changedurl, $lastslash));
}

$url = URL_PUBLIC;
define('BASE_URL', URL_PUBLIC . (endsWith(URL_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
define('BASE_URI', URI_PUBLIC . (endsWith(URI_PUBLIC, '/') ? '': '/') . (USE_MOD_REWRITE ? '': '?'));
defined('THEMES_ROOT')  or define('THEMES_ROOT', CMS_ROOT.DS.'themes'.DS. THEME .DS); 
defined('THEMES_URI')   or define('THEMES_URI', URI_PUBLIC.'themes/'. THEME  . '/'); 
defined('TEMPLATEPATH') or define('TEMPLATEPATH', CMS_ROOT. DS . 'templates' . DS );
defined('CACHEPATH')    or define('CACHEPATH', CMS_ROOT. DS . 'cache' . DS );
defined('URL_SUFFIX')   or define('URL_SUFFIX', '');

ini_set('date.timezone', DEFAULT_TIMEZONE);
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set(DEFAULT_TIMEZONE);
}

// run everything!
require_once(CMS_ROOT. DS .'main.php');

main();

/**
 * Explode an URI and make a array of params
 */
function explode_uri($uri) {
    return preg_split('/\//', $uri, -1, PREG_SPLIT_NO_EMPTY);
}

function url_match($url) {
    $url = trim($url, '/');

    if (CURRENT_URI == $url)
        return true;

    return false;
}

function url_start_with($url) {
    $url = trim($url, '/');

    if (CURRENT_URI == $url)
        return true;

    if (strpos(CURRENT_URI, $url) === 0)
        return true;

    return false;
}

function endsWith($haystack, $needle) {
    return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
}

?>
