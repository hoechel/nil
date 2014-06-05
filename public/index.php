<?php
interface System_Init
{
    const CONFIG_DIRECTORY = 'config';
    const INIT_EXTENSION = 'ini.php';
    const CONSTANTS_INIT_FILENAME = 'constants';
    const CONTROLLER_INIT_FILENAME = 'controller';
    const MODEL_INIT_FILENAME = 'model';
    
    public static function Get_System_Root($execution = __FILE__);
    public static function Get_Project_Name($root = FALSE);  
}

interface System_Config
{
    const DEFAULT_BOOTSTRAP_FILENAME = 'bootstrap';
    const DEFAULT_ERROR_FILENAME = 'error';
    const DEFAULT_EXCEPTION_FILENAME = 'exceptions';
    const DEFAULT_FILE_EXTENSION = 'php'; 
    const DEFAULT_CLASS_EXTENSION = 'class.php';
    const DEFAULT_LOG_EXTENSION = 'log';
    const DEFAULT_CONTROLLER_POSTFIX = '_controller';          
}

interface System_Debug
{
    public static function Dump_System_Constants();
    
    public static function Handle_Exception
    (
        Exception $catched_exception
    );    
}

interface System_Control
{
    public static function Require_Files($files, $extensions = FALSE);
    public static function Call_Hook();   
}

abstract class Value_Control
{
    public static function Strip_Slashes_From_Array($array)
    {
        foreach ($array AS $key => $value)
        {
            $value = stripslahes($value);
            $array[$key] = $value;
        }
        
        return $array;    
    }    
}

abstract class System_Default
implements System_Init, System_Config, System_Debug, System_Control
{
    protected static $init_file = PROJECT_NAME;
    
    abstract static function __autoload($class_name);
    
    public static function Get_System_Root($execution = __FILE__)
    {
        $mirrored_directory_separator = (DIRECTORY_SEPARATOR != '/')
            ? '/'
            : '\\';
        
        $path = str_replace
        (
            str_replace
            (
                $mirrored_directory_separator,
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
    
    public static function Get_Project_Name($root = FALSE)
    {
        if (!$root)
        {
            $root = (defined('ROOT'))
                ? ROOT
                : self::Get_System_Root();
        }
        
        if (is_dir($root))
        {
            $project_name = basename($root);
        }
        else
        {
            throw new Exception
            (
                'System\'s root ' . $root . ' is not a directory.'
            );
        }
        
        return $project_name;      
    }
    
    public static function Dump_System_Constants()
    {
        if (defined('DEVELOPMENT_MODE') AND DEVELOPMENT_MODE)
        {
            $reflect = new ReflectionClass(get_class());
            $class_constants = $reflect->getConstants();
            $defined_constants = get_defined_constants(TRUE);
            
            $dump_constants = var_dump
            (
                array_merge
                (
                    $class_constants,
                    $defined_constants['user']
                )
            );
        }
        else
        {
            $dump_constants = FALSE;
            
            throw new Exception('Development mode is not running.');
        }
        
        return $dump_constants;
    }
    
    public static function Handle_Exception(Exception $catched_exception)
    {
        $exception_message =
            '[' . date('Y-m-d H:i:s'). '] Catched Exception in ' .
            $catched_exception->getFile() . ' on Line ' .
            $catched_exception->getLine() . ': ' .
            $catched_exception->getMessage() . PHP_EOL;
            
        if (defined('DEVELOPMENT_MODE') AND DEVELOPMENT_MODE)
        {
            exit ($exception_message);
        }
        else
        {
            $exception_log_file = (defined('LOG_PATH'))
                ? LOG_PATH . DIRECTORY_SEPARATOR
                : self::Get_System_Root() . DIRECTORY_SEPARATOR;
            
            $exception_log_file .=
            (
                defined('EXCEPTION_FILENAME') AND
                defined('LOG_EXTENSSION')
            )
                ? self::Add_Extension(EXCEPTION_FILENAME, LOG_EXTENSION)
                : self::Add_Extension
                (
                    self::DEFAULT_EXCEPTION_FILENAME,
                    self::DEFAULT_LOG_EXTENSION
                );
            
            $handle = fopen($exception_log_file, 'a');
            
            fwrite($handle, $exception_message);
            fclose($handle);
            
            exit ();   
        }
        
        return TRUE;
    }
    
    public static function Require_Files($files, $extensions = FALSE)
    {
        if (!is_array($files))
        {
            $files = array ($files);
        }
        
        if (!$extensions)
        {
            $extensions = (defined('FILE_EXTENSION'))
                ? FILE_EXTENSION
                : self::DEFAULT_FILE_EXTENSION;
            
            $extensions = array ($extensions);    
        }
        elseif (!is_array($extensions) OR count($extensions) == 1)
        {
            $saved_extension = (!is_array($extensions))
                ? $extensions
                : $extensions[0];
                
            $extensions = array ();
            
            for ($i = 0; $i < count($files); $i++)
            {
                array_push($extensions, $saved_extension);
            }    
        }
        
        $file_system = self::Map_File_System();
        
        $files = array_map
        (
            array ('self', 'Add_Extension'),
            $files,
            $extensions
        );
        
        foreach ($files AS $data)
        {
            $needle = array_map('preg_quote', array ($data));
            
            foreach($needle AS $pattern)
            {
                if ($match = preg_grep('/' . $pattern . '/', $file_system))
                {
                    foreach ($match AS $file)
                    {
                        require_once $file;
                    }
                    
                }
                else
                {
                    throw new Exception
                    (
                        'Required file ' . $data . ' not found in system.'
                    );    
                }    
            }
        }
        
        return TRUE;   
    }
    
    public static function Call_Hook()
    {
        $controller = PROJECT_NAME;
        $action = '__construct';
        $query = NULL;
        
        $mirrored_directory_separator = (DIRECTORY_SEPARATOR != '/')
            ? '/'
            : '\\';
        
        if (array_key_exists('PATH_INFO', $_SERVER))
        {
            $url = explode
            (
                $mirrored_directory_separator,
                pathinfo
                (
                    $_SERVER['PATH_INFO'],
                    PATHINFO_DIRNAME
                )
            );
        
            if (!isset ($url[1]))
            {
                $controller = pathinfo
                (
                    $_SERVER['PATH_INFO'],
                    PATHINFO_FILENAME
                );
            }
            elseif(!isset ($url[2]))
            {
                array_shift($url);
                
                $controller = $url[0];
                
                $action =  pathinfo
                (
                    $_SERVER['PATH_INFO'],
                    PATHINFO_FILENAME
                );
            }
            else
            {
                array_shift($url);
                
                $controller = $url[0];
                
                array_shift($url);
                
                $action = $url[0];
                
                $query =  pathinfo
                (
                    $_SERVER['PATH_INFO'],
                    PATHINFO_FILENAME
                );     
            }
        }
            
        $model = $controller;
        
        $controller .= (defined('CONTROLLER_POSTFIX'))
            ? CONTROLLER_POSTFIX
            : self::DEFAULT_CONTROLLER_POSTFIX;
            
        $mvc = new $controller($model);
        
        if (!(method_exists($controller, $action)))
        {
            throw new Exception
            (
                'Method ' . $action .
                ' does not exist in class ' .
                $controller . '.'
            );
        }
        
        return TRUE;
    }
    
    protected static function Add_Extension($file, $extension)
    {
        $file .= '.' . $extension;
        
        return $file;
    }
    
    protected static function Init_Project_File
    (
        $filename,
        $path,
        $extension,
        $init_file
    )
    {
        $file = $path . self::Add_Extension($filename, $extension);

        if (!file_exists($file))
        {
            $handle = fopen($file, 'w');
            $data = file($init_file);
            
            fwrite($handle, preg_replace
            (
                '/{PROJECT_NAME}/',
                ucwords(PROJECT_NAME),
                implode($data)
            ));
            
            fclose($handle);    
        }
    }
    
    private function Map_File_System
    (
        $exclusion = array ('.', '..'),
        $parent = FALSE
    )
    {
        if (!$parent)
        {
            $parent = (defined('ROOT'))
                ? ROOT
                : self::Get_System_Root();
        }
        
        $file_system = array ();
        $handle = opendir($parent . DIRECTORY_SEPARATOR);
        
        while (($res = readdir($handle)) !== FALSE)
        {
            if (!in_array($res, $exclusion))
            {
                $data = $parent . DIRECTORY_SEPARATOR . $res;
                
                if (is_dir($data))
                {
                    $file_system = array_merge_recursive
                    (
                        $file_system,
                        self::Map_File_System($exclusion, $data)
                    );
                }
                else
                {
                    array_push($file_system, $data);
                }
            }
        }
        
        closedir($handle);
        
        return $file_system;
    }
    
    private function Remove_Magic_Quotes()
    {
        if ($magic_quotes = get_magic_quotes_gpc())
        {
            $_GET = Value_Control::Strip_Slashes_From_Array($_GET);
            $_POST = Value_Control::Strip_Slashes_From_Array($_POST);
            $_COOKIE = Value_Control::Strip_Slashes_From_Array($_COOKIE);
        }
        
        return TRUE;    
    }
    
    private function Unregister_Globals($key_exceptions = array
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
    ))
    {
        if ($register_globals = ini_get('register_globals'))
        {
            foreach ($GLOBALS AS $key => $value)
            {
                if
                (
                    !in_array($key, $key_exceptions) AND
                    isset ($GLOBALS[$key])
                )
                {
                    unset ($GLOBALS[$key]);
                }
            }
        }
        
        return TRUE;    
    }
    
    private function Set_Error_Reporting
    (
        $development_mode = FALSE,
        $error_log_file = FALSE
    )
    {
        if (defined('DEVELOPMENT_MODE'))
        {
            $development_mode = DEVELOPMENT_MODE;
        }
        
        $error_log_file = (defined('LOG_PATH'))
            ? LOG_PATH . DIRECTORY_SEPARATOR
            : self::Get_System_Root() . DIRECTORY_SEPARATOR;
        
        $error_log_file .=
        (
            defined('ERROR_FILENAME') AND
            defined('LOG_EXTENSSION')
        )
            ? self::Add_Extension(ERROR_FILENAME, LOG_EXTENSION)
            : self::Add_Extension
            (
                self::DEFAULT_ERROR_FILENAME,
                self::DEFAULT_LOG_EXTENSION
            );
        
        error_reporting(E_ALL);
        
        if ($development_mode === TRUE)
        {
            ini_set('display_errors', 'On');
        }
        else
        {
            if (!file_exists($error_log_file))
            {
                $handle = fopen($error_log_file, 'w');
                
                fclose($handle);
            }
            
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', $error_log_file);
        }
               
        return TRUE;    
    }   
}

final class System
extends System_Default
{
    public function __construct()
    {
        define('ROOT', self::Get_System_Root());
        
        set_exception_handler('System::Handle_Exception');
          
        if (!($autoload = spl_autoload_register('self::__autoload')))
        {
            throw new Exception
            (
                'Registering __autoload as autoload implementation failed.'
            );
        }
        
        $config_path =
            ROOT . DIRECTORY_SEPARATOR .
            self::CONFIG_DIRECTORY . DIRECTORY_SEPARATOR;
            
        $constants_init_file =
            ROOT . DIRECTORY_SEPARATOR .
            self::CONFIG_DIRECTORY . DIRECTORY_SEPARATOR .
            self::Add_Extension
            (
                self::CONSTANTS_INIT_FILENAME,
                self::INIT_EXTENSION
            );
        
        $init_constants_file = self::Init_Project_File
        (
            PROJECT_NAME,
            $config_path,
            self::INIT_EXTENSION,
            $constants_init_file
        );
        
        if (!($require_init_file = self::Require_Files
        (
            self::$init_file,
            self::INIT_EXTENSION
        )))
        {
            throw new Exception
            (
                'Requiring file ' .
                self::$init_file . '.' . self::INIT_EXTENSION .
                ' failed.'
            );        
        }
         
        $controller_filename = (defined('CONTROLLER_POSTFIX'))
            ? PROJECT_NAME . CONTROLLER_POSTFIX
            : PROJECT_NAME . self::DEFAULT_CONTROLLER_POSTFIX;
        
        $controllers_path = (defined('CONTROLLERS_PATH'))
            ? CONTROLLERS_PATH . DIRECTORY_SEPARATOR
            : ROOT . DIRECTORY_SEPARATOR;
        
        $models_path = (defined('MODELS_PATH'))
            ? MODELS_PATH . DIRECTORY_SEPARATOR
            : ROOT . DIRECTORY_SEPARATOR;
            
        $class_extension = (defined('CLASS_EXTENSION'))
            ? CLASS_EXTENSION
            : self::DEFAULT_CLASS_EXTENSION;
        
        $controller_init_file =
            $config_path .
            self::Add_Extension
            (
                self::CONTROLLER_INIT_FILENAME,
                self::INIT_EXTENSION
            );
            
        $model_init_file =
            $config_path .
            self::Add_Extension
            (
                self::MODEL_INIT_FILENAME,
                self::INIT_EXTENSION
            );
        
        $init_controller_file = self::Init_Project_File
        (
            $controller_filename,
            $controllers_path,
            $class_extension,
            $controller_init_file
        );
        
        $init_model_file = self::Init_Project_File
        (
            PROJECT_NAME,
            $models_path,
            $class_extension,
            $model_init_file
        );
        
        $bootstrap_filename = (defined('BOOTSTRAP_FILENAME'))
            ? BOOTSTRAP_FILENAME
            : self::DEFAULT_BOOTSTRAP_FILENAME;
            
        $file_extension = (defined('FILE_EXTENSION'))
            ? FILE_EXTENSION
            : self::DEFAULT_FILE_EXTENSION;
        
        if (!($require_bootstrap_file = self::Require_Files
        (
            $bootstrap_filename,
            $file_extension
        )))
        {
            throw new Exception
            (
                'Requiring file ' .
                $bootstrap_filename . '.' . $file_extension .
                ' failed.'
            );
        }        
        
        return TRUE;        
    }
    
    public static function __autoload($class_name)
    {
        $class_extension = (defined('CLASS_EXTENSION'))
            ? CLASS_EXTENSION
            : self::DEFAULT_CLASS_EXTENSION;
        
        if (!($require_class_file = self::Require_Files
        (
            strtolower($class_name),
            $class_extension
        )))
        {
            throw new Exception
            (
                'Requiring file ' .
                strtolower($class_name) . '.' . $class_extension .
                ' failed'
            );
        }

        return TRUE;    
    }
}

define('PROJECT_NAME', System::Get_Project_Name());

${PROJECT_NAME} = new System;