<?php
interface SystemInit
{
	const INIT_EXTENSION = 'ini.php';
	
	public static function GetSystemRoot($execution = __FILE__);
	public static function GetProjectName($root = false);
}

interface SystemConfig
{
	const DEFAULT_BOOTSTRAP = 'bootstrap';
	const DEFAULT_CLASS_EXTENSION = 'class.php';
	const DEFAULT_ERROR = 'error';
	const DEFAULT_LOG_EXTENSION = 'log';
	const DEFAULT_REQUIRE_EXTENSION = 'php';
}

interface SystemDebugging
{
	public static function DumpSystemConstants();
}

interface SystemControl
{
	const MAP_DIR_EXCLUSION = '.,..';
	
	public static function RequireFile($file, $extension);
}

abstract class ValueControl
{
	public static function StripSlashesArray($array)
	{
		foreach ($array as $key => $value)
		{
			$value = stripslashes($value);
			$array[$key] = $value;
		}
		
		return $array;
	}
}

abstract class SystemDefault implements SystemInit, SystemConfig, SystemDebugging, SystemControl
{
    private static $dirStructure;
	protected static $initFile = PROJECT_NAME;
		
	abstract static function __autoload($className);
	
	public static function GetSystemRoot($execution = __FILE__)
	{
        $mirroredDS = (DIRECTORY_SEPARATOR != '/')
			? '/'
			: '\\';

		$path = str_replace
        (
			str_replace
            (
                $mirroredDS,
                DIRECTORY_SEPARATOR,
                $_SERVER['DOCUMENT_ROOT']
            ),
			'',
			$execution
		);

		if ($path[0] == DIRECTORY_SEPARATOR)
		{
			$path = substr($path, 1);
		}

		$depth = substr_count($path, DIRECTORY_SEPARATOR);
		$root = $execution;
		
		for ($i = 0; $i < $depth; $i++)
		{
			$root = dirname($root);
		}

		return $root;
	}
	
	public static function GetProjectName($root = false)
	{
		if (!$root)
		{
			$root = (defined('ROOT'))
				? ROOT
				: self::GetSystemRoot();
		}
		
		$name = (is_dir($root))
			? basename($root)
			: false;
		
		return $name;
	}
	
	public static function DumpSystemConstants()
	{
		if (defined('DEVELOPMENT_MODE') and DEVELOPMENT_MODE)
		{
			$reflect = new ReflectionClass(get_class());
			$classConstants = $reflect->getConstants();
			$definedConstants = get_defined_constants(true);
			
            $dumpConstants = var_dump
            (
				array_merge
                (
					$classConstants,
					$definedConstants['user']
				)
			);
		}
		else
		{
			$dumpConstants = die ('ERROR: Not in development mode');
		}

		return $dumpConstants;
	}
	
	private function SetErrorReporting($developmentMode = false, $errorLog = false)
	{
		if (defined('DEVELOPMENT_MODE'))
		{
			$developmentMode = DEVELOPMENT_MODE;
		}
		
		$errorLog = (defined('LOG_PATH'))
			? LOG_PATH . DIRECTORY_SEPARATOR
			: self::GetSystemRoot() . DIRECTORY_SEPARATOR;
			
		$errorLog .= (defined('ERROR') and defined('LOG_EXTENSION'))
			? self::AddExtension(ERROR, LOG_EXTENSION)
			: self::AddExtension(self::DEFAULT_ERROR, self::DEFAULT_LOG_EXTENSION);
			
		error_reporting(E_ALL);
		
		if ($developmentMode === true)
		{
			ini_set('display_errors', 'On');
		}
		else
		{
			ini_set('display_errors', 'Off');
			ini_set('log_errors', 'On');
			ini_set('error_', $errorLog);
		}

		return true;
	}
	
	private function RemoveMagicQuotes()
	{
		if ($magicQuotes = get_magic_quotes_gpc())
		{
			$_GET = ValueControl::StripSlashesArray($_GET);
			$_POST = ValueControl::StripSlashesArray($_POST);
			$_COOKIE = ValueControl::StripSlashesArray($_COOKIE);
		}
		
		return $magicQuotes;
	}
	
	private function UnregisterGlobals
    (
		$exceptions = array
        (
			'GLOBALS',
			'_SESSION',
			'_POST',
			'_GET',
			'_COOKIE',
			'_REQUEST',
			'_SERVER',
			'_ENV',
			'_FILES'
		)
	)
	{
		if ($registerGlobals = ini_get('register_globals'))
		{
			foreach ($GLOBALS as $key => $value)
			{
				if (!in_array($key, $exceptions) and isset($GLOBALS[$key]))
				{
					unset ($GLOBALS[$key]);
				}
			}
		}
	
		return $registerGlobals;
	}	
	
	public static function RequireFile($file, $extension)
	{
		$dirStructure = self::MapDirStructure(self::MAP_DIR_EXCLUSION);
        
		$file = array_map
        (
			array ('self', 'AddExtension'),
			array ($file),
			array ($extension)
		);

		foreach ($file as $value)
		{
			$needle = array_map('preg_quote', array ($value));
			
			foreach ($needle as $pattern)
			{
				($match = implode(preg_grep('/' . $pattern . '/', $dirStructure)))
					? require_once ($match)
					: die ('ERROR: Could not find ' . $value);
			}
		}
		
		return true;
	}
	
	private function MapDirStructure($exclusion, $parent = false)
	{
		if (!is_array($exclusion))
		{
			$exclusion = array_map('trim', explode(',', $exclusion));
		}
		
		if (!$parent)
		{
			$parent = (defined('ROOT'))
				? ROOT
				: self::GetSystemRoot();
		}
		
		$structure = array ();
		$handle = opendir($parent . DIRECTORY_SEPARATOR);
		
		while (($res = readdir($handle)) !== false)
		{
			if (!in_array($res, $exclusion))
			{
				$data = $parent . DIRECTORY_SEPARATOR . $res;
				
				if (is_dir($data))
				{
					$structure = array_merge_recursive
                    (
						$structure,
						self::MapDirStructure($exclusion, $data)
					);
				}
				else
				{
					array_push($structure, $data);
				}
			}
		}
		
		closedir($handle);
        
		return $structure;
	}
	
	private function AddExtension($file, $extension)
	{
		$file .= '.' . $extension;
		
		return $file;
	}
}

final class System extends SystemDefault
{
	public function __construct()
	{
		define('ROOT', self::GetSystemRoot());
			
		$bootstrap = (defined('BOOTSTRAP'))
			? BOOTSTRAP
			: self::DEFAULT_BOOTSTRAP;
		
		$requireExtension = (defined('REQUIRE_EXTENSION'))
			? REQUIRE_EXTENSION
			: self::DEFAULT_REQUIRE_EXTENSION;
            
		$requireInit = self::RequireFile(self::$initFile, self::INIT_EXTENSION);
		$autoload = spl_autoload_register('self::__autoload');
		$requireBootstrap = self::RequireFile($bootstrap, $requireExtension);
		
		return true;
	}

	public static function __autoload($className)
	{
		$classExtension = (defined('CLASS_EXTENSION'))
			? CLASS_EXTENSION
			: self::DEFAULT_CLASS_EXTENSION;
		
	 	$requireClass = self::RequireFile(strtolower($className), $classExtension);
        
		return true;
	}
}

define('PROJECT_NAME', System::GetProjectName());

${PROJECT_NAME} = new System;