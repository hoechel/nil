<?php
/**
 * @todo File 'nil.ini.php'
 * 
 * @category	Framework core
 * @author		Frank Hoechel <hoechel@gmail.com>
 * @copyright	Copyright (c) 2011/2012 NIL
 */

define('SHARED', 'shared');
define('REQUIRE_EXTENSION', 'php');
define('TEMP_DIR', 'temp');
define('LOG_DIR', 'logs');
define('LOG_PATH', ROOT . DIRECTORY_SEPARATOR . TEMP_DIR . DIRECTORY_SEPARATOR . LOG_DIR);
define('DEVELOPMENT_MODE', TRUE);
	
/*	
function trace_project_url()
{
	$url = (array_key_exists('PATH_INFO', $_SERVER))
		? pathinfo($_SERVER['PATH_INFO'], PATHINFO_FILENAME)
		: NULL;
	
	return($url);
}
*/	