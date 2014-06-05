<?php
abstract class Bootstrap
{
    const CONFIG_DIR = 'config';
    const LIBRARY_DIR = 'library';
    const CONSTANTS_FILE = 'constants';
    const BOOTSTRAP_FILE = 'bootstrap';
    const INI_EXT = 'ini.php';
    const PHP_EXT = 'php';
    const CONTROLLER_FILE = 'controller';
    const MODEL_FILE = 'model';
    const DATABASE_FILE = 'database';
    const CLASS_INI_EXT = 'class.ini.php';
     
    final public static function Set_Error_Reporting
    (
        $errors_file = FALSE,
        $dev_mode = DEV_MODE
    )
    {
        if ( !$errors_file )
        {
            $errors_file = Adjustment::Chain_Path(array
            (
                ROOT,
                TEMP_DIR,
                LOGS_DIR,
                Adjustment::Chain_Filename
                (
                    ERRORS_FILE,
                    LOG_EXT
                )
            ));
        }
        
        error_reporting(E_ALL);
        
        if ( $dev_mode === TRUE )
        {
            ini_set('display_errors', 'On');
        }
        else
        {
            $create_file = File_System::Create_Files(array ($errors_file));
            
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', $errors_file);
        }
               
        return TRUE;    
    }   
     
    final public static function Remove_Magic_Quotes
    (
        $args = array('_COOKIE', '_GET', '_POST')
    )
    {        
        if ( $magic_quotes = get_magic_quotes_gpc() )
        {
            foreach ( $args AS $array )
            {
                if ( isset (${$array}) )
                {
                    ${$array} = Adjustment::Strip_Slashes_From_Array
                    (
                        ${$array}
                    );     
                }
            }
        }
        
        return $magic_quotes;          
    }
     
    final public static function Unregister_Globals
    (
        $exceptions = array
        (
            'GLOBALS',
            '_COOKIE',
            '_ENV',
            '_FILES',
            '_GET',
            '_POST',
            '_REQUEST',
            '_SERVER',
            '_SESSION')
    )
    {
        if ( $register_globals = ini_get('register_globals') )
        {
            foreach ( $GLOBALS AS $key => $value )
            {
                if
                (
                    isset ($GLOBALS[$key]) AND
                    !in_array($key, $exceptions)
                )
                {
                    unset ($GLOBALS[$key]);
                }
            }
        }
        
        return $register_globals;    
    }
     
    final public static function Call_Hook()
    {
        $controller_class = ucwords(PROJECT_NAME) .
            ucwords(CONTROLLER_SUFFIX);
        $controller = new $controller_class();
        
        return TRUE;
    }    
}
 
abstract class Debugger
{
    const DEF_TEMP_DIR = 'temp';
    const DEF_LOGS_DIR = 'logs';
    const DEF_EXCEPTIONS_FILE = 'exceptions';
    const DEF_LOG_EXT = 'log';
     
    final public static function Catch_Exception($exception)
    {
        $message = '[' . date('Y-m-d H:i:s') . '] ' .
            'Catched Exception in ' .
            $exception->getFile() . ' on Line ' .
            $exception->getLine() . ': ' .
            $exception->getMessage() . '.' .
            PHP_EOL;
            
        if ( defined('DEV_MODE') AND DEV_MODE )
        {
            exit ($message);
        }
        else
        {
            if ( defined('EXCEPTIONS_FILE') )
            {
                $root = ROOT;
                $temp_dir = TEMP_DIR;
                $logs_dir = LOGS_DIR;
                $exceptions_file = EXCEPTIONS_FILE;
                $log_ext = LOG_EXT;
            }
            else
            {
                $root = File_System::Get_Root();
                $temp_dir = self::DEF_TEMP_DIR;
                $logs_dir = self::DEF_LOGS_DIR;
                $exceptions_file = self::DEF_EXCEPTIONS_FILE;
                $log_ext = self::DEF_LOG_EXT;    
            }
            
            $exceptions_file = Adjustment::Chain_Path
            (
                array
                (
                    $root,
                    $temp_dir,
                    $logs_dir,
                    Adjustment::Chain_Filename($exceptions_file, $log_ext)
                )
            );
            
            File_System::Append_To_Files
            (
                array ($exceptions_file => $message)
            );
            
            exit ();   
        }
        
        return TRUE;
    }
     
    final public static function Dump_Constants($object = NULL)
    {
        if ( defined('DEV_MODE') AND DEV_MODE )
        {
            $reflect = new ReflectionClass(get_class($object));
            $class_constants = $reflect->getConstants();
            $defined_constants = get_defined_constants(TRUE);
            $dump_constants = var_dump
            (
                array_merge($class_constants, $defined_constants['user'])
            );
        }
        else
        {
            $message = Message_Stock::E_NO_DEV_MODE;
            
            throw new Exception($message);
        }
        
        return $dump_constants;    
    }
}
 
abstract class File_System
{   
    final public static function Get_Project_Name($root = FALSE)
    {
        if ( !$root )
        {
            $root = ( defined('ROOT') )
                ? ROOT
                : self::Get_Root();    
        }
            
        if ( is_dir($root) )
        {
            $project_name = basename($root);
        }
        else
        {
            $message = Message_Stock::E_NO_DIR;
                
            throw new Exception(vsprintf($message, array ($root)));  
        }
        
        return $project_name;
    }
     
    final public static function Get_Root($path = __FILE__)
    {
        $root = $path;
        $path = str_replace
        (
            Adjustment::Level_Out_Path($_SERVER['DOCUMENT_ROOT']),
            NULL, Adjustment::Level_Out_Path($path)
        ); 
        $n = substr_count($path, DIRECTORY_SEPARATOR);

        for ( $i = 0; $i < $n; $i++ )
        {
            $root = dirname($root);
        }
        
        return $root;
    }
     
    final public static function Require_Files($files)
    {
        $file_system = self::Map_File_System();

        foreach ( $files AS $data )
        {
            $needle = array_map('preg_quote', array ($data));
            
            foreach ( $needle AS $pattern )
            {
                $match = preg_grep('/' . $pattern . '/', $file_system);
                
                foreach ( $match AS $file )
                {
                    if ( file_exists($file) )
                    {
                        require_once $file;    
                    }
                    else
                    {
                        $message = Message_Stock::E_NO_FILE;
                    
                        throw new Exception
                        (
                            vsprintf($message, array ($data))
                        );   
                    } 
                }
            }
        }
        
        return TRUE;    
    }
     
    final public static function Copy_Files
    (
        $args,
        $file_protection = FALSE
    )
    {
        foreach ( $args AS $file )
        {
            $source = $file[0];
            $target = $file[1];

            if ( file_exists($target) AND $file_protection )
            {
                $message = Message_Stock::E_FILE_PROTECTED;
                
                throw new Exception(vsprintf($message, array ($target)));  
            }
            else
            {
                $handle = fopen($target, 'w');
                
                if ( file_exists($source) )
                {
                    $data = file($source);
                    
                    if ( defined('DATABASE_SERVER') )
                    {
                        foreach ( $data AS $line => $value )
                        {
                            $data[$line] = preg_replace
                            (
                                '/{DATABASE_SERVER}/',
                                DATABASE_SERVER,
                                $data[$line]
                            );    
                        }
                        
                    }

                    fwrite
                    (
                        $handle,
                        preg_replace
                        (
                            '/{PROJECT_NAME}/',
                            ucwords(PROJECT_NAME),
                            implode($data)
                        )
                    );
                }
                else
                {
                    $message = Message_Stock::E_NO_FILE;
                
                    throw new Exception
                    (
                        vsprintf($message, array ($source))
                    );      
                }
                
                fclose($handle);    
            } 
        }

        return TRUE;
    }
     
    final public static function Create_Files($args, $exception = FALSE)
    {
        foreach ( $args AS $file )
        {
            if ( !file_exists($file) )
            {
                $handle = fopen($file, 'w');
                
                fclose($handle);
            }
            elseif ( $exception )
            {
                $message = Message_Stock::E_FILE_EXISTS;
                
                throw new Exception
                (
                    vsprintf($message, array ($file))
                );     
            } 
        }
        
        return TRUE;
    }
     
    final public static function Append_To_Files($args)
    {
        foreach ( $args AS $file => $appendix )
        {
            $handle = fopen($file, 'a');
            
            fwrite($handle, $appendix);
            fclose($handle);
        }

        return TRUE;
    }
    
    final public static function Load_Files_To_String($array = array ())
    {
        $files_to_string = NULL;
        
        foreach ( $array AS $file )
        {
            if ( file_exists($file) )
            {
                $data = implode(file($file));
                $files_to_string .= $data . PHP_EOL;
            }
            else
            {
                $message = Message_Stock::E_NO_FILE;
                
                throw new Exception(vsprintf($message, array ($file)));
            }
        }
        
        return $files_to_string;
    }
     
    final private static function Map_File_System
    (
        $exclusion = array ('.', '..'),
        $parent = ROOT
    )
    {       
        $file_system = array ();
        $handle = opendir($parent . DIRECTORY_SEPARATOR);
        
        while ( ($data = readdir($handle)) !== FALSE )
        {
            if ( !in_array($data, $exclusion) )
            {
                $path = Adjustment::Chain_Path(array ($parent, $data));
                
                if ( is_dir($path) )
                {
                    $file_system = array_merge_recursive
                    (
                        $file_system,
                        self::Map_File_System($exclusion, $path)
                    );
                }
                else
                {
                    array_push($file_system, $path);
                }
            }
        }
        
        closedir($handle);
        
        return $file_system;
    }
    
    final public static function Search_File_System
    (
        $pattern,
        $exclusion = array ()
    )
    {
        $file_system = self::Map_File_System();
        $search_result = array ();
        
        if
        (
            $match = preg_grep
            (
                '/' . preg_quote($pattern) . '/',
                $file_system
            )
        )      
        {
            foreach ( $match AS $file )
            {
                if ( !in_array($file, $exclusion) )
                {
                    array_push($search_result, $file);    
                }
            }
        }
                  
        return $search_result;
    }    
}
 
abstract class Adjustment
{    
    final public static function Chain_Filename($base, $extension)
    {
        $filename = $base . '.' . $extension;

        return $filename;    
    }
     
    final public static function Chain_Path($args)
    {
        $path = NULL;
        
        foreach ( $args AS $data )
        {
            $path .= DIRECTORY_SEPARATOR . $data;
        }
        
        $path = substr($path, 1);

        return $path;
    }
     
    final public static function Level_Out_Path($path)
    {   
        $mirrored_directory_separator = ( DIRECTORY_SEPARATOR != '/' )
            ? '/'
            : '\\';
            
        $path = str_replace
        (
            $mirrored_directory_separator,
            DIRECTORY_SEPARATOR,
            $path
        );
            
        if ( $path[0] == DIRECTORY_SEPARATOR )
        {
            $path = substr($path, 1);
        }    
        
        return $path;
    }
     
    final public static function Strip_Slashes_From_Array
    (
        $array = array ()
    )
    {
        foreach ( $array AS $key => $value )
        {
            if ( is_array($array[$key]) )
            {
                $array[$key] = self::Strip_Slashes_From_Array
                (
                    $array[$key]
                );
            }
            else
            {
                $value = stripslashes($value);
                $array[$key] = $value;
            }
        }
        
        return $array;    
    } 
}
 
abstract class Message_Stock
{
    const E_NO_DIR = '\'%s\' is not a directory';
    const E_NO_DEV_MODE = 'Development mode is not running';
    const E_NO_FILE = '\'%s\' not found in file system';
    const E_FILE_PROTECTED = 'File \'%s\' is protected from overwriting';
    const E_FILE_EXISTS = 'File \'%s\' exists already';
    const E_NO_CLASS = 'Class \'%s\' have not been found';
    const E_NO_DB_COND_YES =
        'Access denied for user \'%s\'@\'%s\' (using password: YES)';
    const E_NO_DB_COND_NO =
        'Access denied for user \'%s\'@\'%s\' (using password: NO)';
    const E_NO_ARR_KEY = 'Array key \'%s\' does not exist';
    const E_NO_SESS = 'Session does not exist';
    const E_DB_CREATION ='Creation of database \'%s\' failed';
    const E_DB_SELECTION = 'Could not select database \'%s\'';
    const E_DB_SYNTAX_ERR = 'You have an error in your SQL syntax';
}

final class Project
extends Bootstrap
{   
    public function __construct()
    {
        spl_autoload_register('self::__autoload');
        define('ROOT', File_System::Get_Root());
        
        $constants_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    self::CONSTANTS_FILE,
                    self::INI_EXT
                )
            )
        );
        $bootstrap_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    self::BOOTSTRAP_FILE,
                    self::INI_EXT
                )
            )
        );
        $controller_class_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    self::CONTROLLER_FILE,
                    self::CLASS_INI_EXT
                )
            )
        );
        $model_class_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    self::MODEL_FILE,
                    self::CLASS_INI_EXT
                )
            )
        );
        $database_class_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    self::DATABASE_FILE,
                    self::CLASS_INI_EXT
                )
            )
        );
        $project_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_Filename
                (
                    PROJECT_NAME,
                    self::INI_EXT
                )
            )
        );
        
        File_System::Copy_Files
        (
            array
            (
                array
                (
                    $constants_ini,
                    $project_ini
                ),
                array
                (
                    $bootstrap_ini,
                    Adjustment::Chain_Path
                    (
                        array
                        (
                            ROOT,
                            self::LIBRARY_DIR,
                            Adjustment::Chain_Filename
                            (
                                self::BOOTSTRAP_FILE,
                                self::PHP_EXT
                            )
                        )
                    )
                )
            )
        );
        File_System::Append_To_Files
        (
            array
            (
                $project_ini =>
                File_System::Load_Files_To_String
                (
                    File_System::Search_File_System
                    (
                        self::INI_EXT,
                        array
                        (
                            $constants_ini,
                            $bootstrap_ini,
                            $controller_class_ini,
                            $model_class_ini,
                            $database_class_ini,
                            $project_ini
                        ) 
                    )
                )    
            )
        );
        File_System::Require_Files
        (
            array
            (
                Adjustment::Chain_Filename(PROJECT_NAME, self::INI_EXT),
                Adjustment::Chain_Filename
                (
                    self::BOOTSTRAP_FILE,
                    self::PHP_EXT
                )
            )
        );
        File_System::Copy_Files
        (
            array
            (
                array
                (
                    $controller_class_ini,
                    Adjustment::Chain_Path
                    (
                        array
                        (
                            ROOT,
                            APPLICATION_DIR,
                            CONTROLLERS_DIR,
                            Adjustment::Chain_Filename
                            (
                                PROJECT_NAME . CONTROLLER_SUFFIX,
                                CLASS_EXT
                            )
                        )
                    )
                ),
                array
                (
                    $model_class_ini,
                    Adjustment::Chain_Path
                    (
                        array
                        (
                            ROOT,
                            APPLICATION_DIR,
                            MODELS_DIR,
                            Adjustment::Chain_Filename
                            (
                                PROJECT_NAME,
                                CLASS_EXT
                            )
                        )
                    )
                ),
                array
                (
                    $database_class_ini,
                    Adjustment::Chain_Path
                    (
                        array
                        (
                            ROOT,
                            self::LIBRARY_DIR,
                            Adjustment::Chain_Filename
                            (
                                self::DATABASE_FILE,
                                CLASS_EXT
                            )
                        )
                    )
                )
            )
        );
    }
     
    function __autoload($class_name)
    {
        File_System::Require_Files
        (
            array
            (
                Adjustment::Chain_Filename
                (
                    strtolower($class_name),
                    CLASS_EXT
                )
            )
        );      
    }
     
    public function __destruct()
    {
        Database::Connect_Host();
        Database::Save_Project_Vars();
        self::Call_Hook();
        Database::Disconnect_Host();
        
        if ( DEV_MODE )
        {
            Debugger::Dump_Constants($this);    
        }   
    }      
}

set_exception_handler('Debugger::Catch_Exception');
define('PROJECT_NAME', File_System::Get_Project_Name());

${PROJECT_NAME} = new Project;