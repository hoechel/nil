<?php
/**
 * Entry point of the program
 * 
 * All server requests run throught this entry point. It instantiates
 * mainly the project object {@link Project}, which allows the
 * bootstrapping {@link Bootstrap}. Therefore required classes
 * {@link File_System}, {@link Debugger}, {@link Message_Stock},
 * {@link Adjustment} are abstracted, which defines necessary class
 * constants and methods.
 * 
 * PHP 5.4.3
 * 
 * Copyright (c) 2012-14 Frank Hoechel
 * 
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @category Initialization
 * @package NIL
 * @subpackage Core
 * @author Frank Hoechel <hoechel@gmail.com>
 * @version $Id: index.php,v 0.5 2014/06/06 12:46:25 fhoechel Exp $
 * @copyright Copyright (c) 2011-14 Frank Hoechel
 * @since Version 0.2
 * @license http://opensource.org/licenses/GPL-3.0 GPL-3.0
 */

/**#@+
 * @access public
 */
   
/**
 * Abtsraction of the file system of the program location
 * 
 * Declarations to get and handle informations of the file system
 * {@link Get_Project_Name}, {@link Get_Root}, {@link Map_File_System},
 * {@link Scan_File_System}, to manipulate it and its files
 * {@link Append_To_Files}, {@link Copy_Files}, {@link Create_Files}
 * and to provide its files for the program runtime
 * {@link Load_Files_To_String}, {@link Require_Files}. 
 * 
 * @package NIL 
 * @subpackage Core 
 * @author Frank Hoechel <hoechel@gmail.com>
 * @version 0.5
 * @copyright Copyright (c) 2011-14 Frank Hoechel
 * @since Version 0.4
 */

abstract class File_System
{
    /**
     * Appends data to files
     * 
     * By checking the existance of files of the passed filenames, this
     * method will ensure that no new files were created. Otherwise the
     * runtime will be stopped by throwing an exception.
     * 
     * @param array $args type hinting; it should contain the filenames
     * as keys and the attached appendices as values
     * @return void
     * @uses Message_Stock::E_NO_FILE
     */
     
    final public static function Append_To_Files(array $args)
    {
        foreach ( $args AS $filename => $appendix )
        {
            if ( file_exists($filename) )
            {
                $handle = fopen($filename, 'a');
            
                fwrite($handle, $appendix);
                fclose($handle);
            }
            else
            {
                $message = vsprintf
                (
                    Message_Stock::E_NO_FILE,
                    array ($filename)
                );
                
                throw new Exception($message);
            }    
        }    
    }
    
    /**
     * Overwrites target file contents with source file contents
     * 
     * By validating the passed pairs of source and target filenames,
     * by checking the existance of source files, and by checking the
     * existance of target files AND the setting of file protection flag,
     * this method will ensure the successful completion of the copy
     * process and will not overwrite the target file content, if it is
     * protected. Otherwise the runtime will be stopped by throwing an
     * exception. It will also erase comments of the source file content
     * before the overwriting process starts, if the erase comments flag
     * demands it.
     * 
     * @param array $args type hinting; it should contain arrays of a
     * pair of source and target filename as values
     * @param bool $file_protection default TRUE; it protects the target
     * files from overwriting
     * @param bool $erase_comments default FALSE; it demands erasing of
     * comments from the source file contents
     * @return void
     * @uses Message_Stock::E_FILE_PROTECTED
     * @uses Adjustment::Erase_Comments()
     * @uses Message_Stock::E_NO_FILE
     * @uses Message_Stock::E_UNVALID_REQUEST
     */
    
    final public static function Copy_Files
    (
        array $args,
        $file_protection = TRUE,
        $erase_comments = FALSE
    )
    {
        foreach ( $args AS $files )
        {
            $n = count($files);
            $pair = 2;
            
            if ( $n == $pair )
            {
                $source = $files[0];
                $target = $files[1];
                
                if ( file_exists($target) AND $file_protection )
                {
                    $message = vsprintf
                    (
                        Message_Stock::E_FILE_PROTECTED,
                        array ($target)
                    );
                    
                    throw new Exception($message);    
                }
                else
                {
                    $handle = fopen($target, 'w');
                    
                    if ( file_exists($source) )
                    {
                        $data = implode(file($source));
                        
                        if ( $erase_comments )
                        {
                            $data = Adjustment::Erase_Comments
                            (
                                $data,
                                $erase_empty_lines = TRUE
                            );
                        }
                        
                        fwrite($handle, $data);
                        fclose($handle);    
                    }
                    else
                    {
                        $message = vsprintf
                        (
                            Message_Stock::E_NO_FILE,
                            array ($source)
                        );
                        
                        throw new Exception($message);
                    }
                }
            }
            else
            {
                $message = Message_Stock::E_UNVALID_REQUEST;
                
                throw new Exception($message);
            }
        } 
    }
    
    /**
     * Creates empty files
     * 
     * By checking the existance of the passed filenames AND the setting
     * of passed file protection flag, this method will ensure the
     * creation of empty files and will not erase the files content, if
     * they already exist and are protected.
     * 
     * @param array $args type hinting; it should contain the filenames
     * as values
     * @param bool $file_protection default TRUE; it protects the already
     * existing files from erasing their content
     * @return void
     */
     
    final public static function Create_Files
    (
        array $args,
        $file_protection = TRUE
    )
    {
        foreach ( $args AS $filename )
        {
            if ( file_exists($filename) AND !$file_protection )
            {
                unlink($filename);
                
                $handle = fopen($filename, 'w');
                
                fclose($handle);     
            }
            elseif ( !file_exists($filename) )
            {
                $handle = fopen($filename, 'w');
                
                fclose($handle);   
            }
        }
    }
    
    /**
     * Sets name of root folder as project's name
     * 
     * By checking possible storages of the root's folder name, this
     * method either sets the project's name to one of the values of the
     * storages or calls {@link Get_Root}. By validating that the root is
     * a folder it returns the project's name or otherwise exits runtime
     * by throwing an exception.
     * 
     * @param string $root default NULL; it should contain the root's
     * folder name
     * @return string
     * @uses File_System::Get_Root()
     * @uses Message_Stock::E_NO_DIR
     */
     
    final public static function Get_Project_Name($root = NULL)
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
            $message = vsprintf(Message_Stock::E_NO_DIR, array ($root));
            
            throw new Exception($message);
        }
        
        return $project_name;      
    }
    
    /**
     * Returns name of root folder
     * 
     * By comparison of the passed parent path and the document's root
     * path and leveling up until the root folder, this method gets and
     * returns the name of the root folder.
     * 
     * @param string $parent default ___FILE__; it should contain the
     * full path of this entry point file
     * @return string
     * @uses Adjustment::Level_Out_Path()
     */
     
    final public static function Get_Root($parent = __FILE__)
    {
        $root = $parent;
        $parent = str_replace(
            Adjustment::Level_Out_Path($_SERVER['DOCUMENT_ROOT']),
            NULL,
            Adjustment::Level_Out_Path($parent)
        );
        
        $n = substr_count($parent, DIRECTORY_SEPARATOR);
        
        for ( $i = 0; $i < $n; $i++ )
        {
            $root = dirname($root);    
        }
        
        return $root;    
    }
    
    /**
     * Collects data of files and assembles them to a string
     * 
     * By checking the existance of passes files, this method
     * implodes the file lines into a string and concatenates all
     * passed files. Exception is thrown and exits runtime if
     * file does not exist.
     * 
     * @param array $files it should contain needed path of files
     * @return string
     * @uses Message_Stock::E_NO_FILE
     */
     
    final public static function Load_Files_To_String(array $files)
    {
        $files_to_string = NULL;
        
        foreach ( $files AS $filename )
        {
            if ( file_exists($filename) )
            {
                $handle = file($filename);
                $files_to_string .= implode($handle) . PHP_EOL;      
            }   
            else
            {
                $message = sprintf
                (
                    Message_Stock::E_NO_FILE,
                    array ($filename)
                );
                
                throw new Exception($message);
            } 
        }
        
        return $files_to_string;
    }
    
    /**
     * Scans project's folders and maps them into an array
     * 
     * By scanning the passed parent folder path, this method maps
     * its file and directory structure into an array if handled data
     * is not part of the exclusions array. Recursion is started if
     * handled data is a directory.
     * 
     * @param array $exclusions type hinting default ('.', '..'); it
     * should contain all folder and file names which shall not be scanned
     * @param string $parent default ROOT; it should contain the
     * project's root path
     * @return array
     * @uses Adjustment::Chain_Path()
     * @uses File_System::Map_File_System()
     */
     
    final private static function Map_File_System
    (
        array $exclusions = array ('.', '..'),
        $parent = ROOT
    )
    {
        $file_system = array ();
        $handle = scandir($parent . DIRECTORY_SEPARATOR);
        
        foreach ( $handle AS $data )
        {
            if ( !in_array($data, $exclusions) )
            {
                $path = Adjustment::Chain_Path(array ($parent, $data));
                
                if ( is_dir($path) )
                {
                    $file_system = array_merge_recursive
                    (
                        $file_system,
                        self::Map_File_System($exclusions, $path)
                    );    
                }
                else
                {
                    array_push($file_system, $path);
                }
            }
        }
        
        return $file_system;
    } 
    
    /**
     * Requiring files from file system 
     * 
     * By checking if passed files exists within mapped and actual
     * file system, this method will ensure that only assigned and 
     * existing project's files were included. Otherwise runtime
     * exists by throwing an exception or just does not require
     * the files.
     * 
     * @param array $files it should contain needed file names
     * @return void
     * @uses File_System::Map_File_System()
     * @uses Message_Stock::E_NO_FILE
     */
     
    final public static function Require_Files(array $files)
    {
        var_dump($files);
        $files = self::Scan_File_System($files);
        
        var_dump($files);
        foreach ( $files AS $filename )
        {
            if ( file_exists($filename) )
            {
                require_once($filename);
            }
            else
            {
                $message = vsprintf
                (
                    Message_Stock::E_NO_FILE,
                    array ($filename)
                );
                
                throw new Exception($message);
            }
        }
    }

    /**
     * File_System::Scan_File_System()
     * 
     * looks for pattern matches within file system.
     * 
     * @param array $args type hinting;
     * @param array $exclusions type hinting;
     * @return array
     */
     
    final public static function Scan_File_System
    (
        array $args,
        array $exclusions = NULL
    )
    {
        $file_system = self::Map_File_System();
        $search_result = array ();
        
        foreach ( $args AS $data )
        {
            $needle = array_map('preg_quote', array ($data) );

            foreach ( $needle AS $pattern )
            {
                $match = preg_grep('/' . $pattern . '$/', $file_system);
                
                foreach ( $match AS $filename )
                {
                    if ( !is_array($exclusions) OR !in_array($filename, $exclusions) )
                    {
                        array_push($search_result, $filename); 
                    }
                }
            }  
        }
        
        return $search_result;
    }
}

/**
 * @package Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Debugger
{
    const DEF_TEMP_DIR = 'temp';
    const DEF_LOGS_DIR = 'logs';
    const DEF_EXCEPTIONS_FILE = 'exceptions';
    const DEF_LOG_EXT = 'log';
    
    /**
     * Debugger::Handle_Exception()
     * 
     * exits runtime with error message.
     * 
     * @param object $exception
     * @return TRUE
     */
     
    final public static function Handle_Exception(Exception $exception)
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
            $root = ( defined('ROOT') )
                ? ROOT
                : File_System::Get_Root();
                
            $temp_dir = ( defined('TEMP_DIR') )
                ? TEMP_DIR
                : self::DEF_TEMP_DIR;
            
            $logs_dir = ( defined('LOGS_DIR') )
                ? LOGS_DIR
                : self::DEF_LOGS_DIR;
            
            $exceptions_file = ( defined('EXCEPTION_FILE') )
                ? EXCEPTION_FILE
                : self::DEF_EXCEPTIONS_FILE;
            
            $log_ext = ( defined('LOG_EXT') )
                ? LOG_EXT
                : self::DEF_LOG_EXT;
            
            $exceptions_filename = Adjustment::Chain_Path
            (
                array
                (
                    $root,
                    $temp_dir,
                    $logs_dir,
                    Adjustment::Chain_File
                    (
                        array ($exceptions_file, $log_ext)
                    )
                )
            );
            
            File_System::Create_Files
            (
                array ($exceptions_filename)
            );
            
            File_System::Append_To_Files
            (
                array ($exceptions_filename => $message)
            );
            
            exit ();
        }
        
        return TRUE;
    }
    
    /**
     * Debugger::Dump_Constants()
     * 
     * dumps project's constants.
     * 
     * @param array $objects
     * @return string
     */
     
    final public static function Dump_Constants(array $args = NULL)
    {
        $dump_constants = NULL;
        
        if ( defined('DEV_MODE') AND DEV_MODE )
        {
            $class_constants = Delivery_Agent::Get_Class_Constants($args);
            $defined_constants = get_defined_constants(TRUE);
            $dump_constants = var_dump
            (
                array_merge($class_constants, $defined_constants['user'])
            );
        }
        
        return $dump_constants;    
    }
}

/**
 * @package Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Message_Stock
{
    const E_NO_DIR = '\'%s\' is not a directory';
    const E_NO_FILE = '\'%s\' not found in file system';
    const E_UNVALID_REQUEST = 'The request ist unvalid or incomplete';
    const E_FILE_PROTECTED = 'File \'%s\' is protected from overwriting';
    const E_SESS_NOT_STARTED = 'Could not start session';
    const E_NO_SESS = 'Session does not exist';
    const E_NO_DB_COND_YES =
        'Access denied for user \'%s\'@\'%s\' (using password: YES)';
    const E_NO_DB_COND_NO =
        'Access denied for user \'%s\'@\'%s\' (using password: NO)';
    const E_DB_CREATION ='Creation of database \'%s\' failed';
    const E_DB_SYNTAX_ERR = 'You have an error in your SQL syntax \'%s\'';
    const E_NO_ARR_KEY = 'Array key \'%s\' does not exist';
    const E_NO_DEV_MODE = 'Development mode is not running';
}

/**
 * @package Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Adjustment
{
    /**
     * Adjustment::Level_Out_Path()
     * 
     * unifies paths.
     * 
     * @param string $path
     * @return string
     */
     
    final public static function Level_Out_Path($path)
    {
        $mirrored_DS = ( DIRECTORY_SEPARATOR != '/' )
            ? '/'
            : '\\';

        $path = str_replace($mirrored_DS, DIRECTORY_SEPARATOR, $path);
        
        if ( $path[0] == DIRECTORY_SEPARATOR )
        {
            $path = substr($path, 1);
        }

        return $path;    
    }
    
    /**
     * Adjustment::Chain_Path()
     * 
     * assembles path parts.
     * 
     * @param array $args
     * @return string 
     */
     
    final public static function Chain_Path(array $args)
    {
        $path = implode(DIRECTORY_SEPARATOR, $args);

        return $path;
    }
    
    /**
     * Adjustment::Chain_File()
     * 
     * assembles file parts.
     * 
     * @param array $args
     * @return string
     */
     
    final public static function Chain_File(array $args)
    {
        $file = implode('.', $args);

        return $file;
    }
    
    /**
     * Adjustment::Chain_PHP_Code()
     * 
     * concatenates php code.
     * 
     * @param array $args
     * @return string
     */
     
    final public static function Chain_PHP_Code
    (
        array $args,
        $erase_comments = FALSE
    )
    {
        $php_code = '<?php' . PHP_EOL;
        $pattern = '(<\?(php)?)|(\?>)';
        
        foreach ( $args AS $data )
        {    
            $needle = preg_replace('/' . $pattern . '/', '', $data);
            $php_code .= trim($needle);
        }
        
        if ( $erase_comments )
        {
            $php_code = Adjustment::Erase_Comments
            (
                $php_code,
                $erase_empty_lines = TRUE
            );   
        }
        
        return $php_code;
    }
    
    /**
     * Adjustment::Erase_Comments()
     * 
     * erases php comment blocks and lines.
     * 
     * @param array $args
     * @return array
     */
     
    final public static function Erase_Comments
    (
        $data,
        $erase_empty_lines = FALSE
    )
    {
        $tokens = token_get_all($data);
        $erase_comments = NULL;
            
        foreach ( $tokens AS $token )
        {
            if
            (
                (
                    $token[0] != T_DOC_COMMENT AND
                    $token[0] != T_COMMENT
                ) AND
                (isset($token[1]) AND $token[1] !== NULL )
            )
            {
                $erase_comments .= $token[1];
            }
            elseif ( $token[0] == T_COMMENT )
            {
                $erase_comments .= PHP_EOL;    
            }
            elseif ( !is_array($token) )
            {
                $erase_comments .= $token;      
            }
        } 
        
        if ( $erase_empty_lines )
        {
            $erase_comments = Adjustment::Erase_Empty_Lines
            (
                $erase_comments
            );   
        }
        
        return $erase_comments;
    }
    
    /**
     * Adjustment::Erase_Empty_Lines()
     * 
     * erases empty lines of a given string.
     * 
     * @param string $data
     * @return string
     */
     
    final private static function Erase_Empty_Lines($data)
    {
        $pattern = '(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+';
        $erase_empty_lines = preg_replace
        (
            '/' . $pattern . '/',
            PHP_EOL,
            $data
        );
        
        return $erase_empty_lines;     
    }
    
    /**
     * Adjustment::Strip_Slashes_From_Array()
     * 
     * strip slashes from arrays recursively.
     * 
     * @param array $array
     * @return array
     */
     
    final public static function Strip_Slashes_From_Array(array $array)
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

/**
 * @package Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
final class Project
extends Bootstrap
{
    /**
     * Project::__construct()
     * 
     * is the constructor of the class.
     * 
     * @return TRUE
     */
     
    final function __construct()
    {
        spl_autoload_register('self::__autoload');
        define('ROOT', File_System::Get_Root());

        $constants_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_File
                (
                    array (self::CONSTANTS_FILE, self::INI_EXT)
                )
            )
        );
        
        $project_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_File
                (
                    array (PROJECT_NAME, self::INI_EXT)
                )
            )
        );
        
        $bootstrap_ini = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                self::CONFIG_DIR,
                Adjustment::Chain_File
                (
                    array (self::BOOTSTRAP_FILE, self::INI_EXT)
                )
            )
        );

        File_System::Create_Files(array ($project_ini), FALSE);
        File_System::Append_To_Files
        (
            array
            (
                $project_ini => 
                Adjustment::Chain_PHP_Code
                (
                    array
                    (
                        File_System::Load_Files_To_String
                        (
                            File_System::Scan_File_System
                            (
                                array (self::INI_EXT),
                                array
                                (
                                    $project_ini,
                                    $bootstrap_ini
                                )
                            )
                        )
                    ),
                    $erase_comments = TRUE
                )     
            )
        );
        
        File_System::Require_Files
        (
            array
            (
                Adjustment::Chain_File
                (
                    array (PROJECT_NAME, self::INI_EXT)
                )
            )
        );    
        
        $bootstrap = Adjustment::Chain_Path
        (
            array
            (
                ROOT,
                LIBRARY_DIR,
                Adjustment::Chain_File
                (
                    array (BOOTSTRAP_FILE, PHP_EXT)
                )
            )
        );
        
        File_System::Copy_Files
        (
            array (array ($bootstrap_ini, $bootstrap)),
            $file_protection = FALSE,
            $erase_comments = TRUE
        );
        
        File_System::Require_Files
        (
            array
            (
                Adjustment::Chain_File
                (
                    array (BOOTSTRAP_FILE, PHP_EXT)
                )
            )
        );
        
        return TRUE;
    }
    
    /**
     * Project::__autoload()
     * 
     * is the magic funtion for including class files.
     * 
     * @param string $class_name
     * @return TRUE
     */
     
    final private static function __autoload($class_name)
    {
        File_System::Require_Files
        (
            array
            (
                Adjustment::Chain_File
                (
                    array (strtolower($class_name), CLASS_EXT)
                )
            )
        );
        
        return TRUE; 
    }
    
    /**
     * Project::__destruct()
     * 
     * is the destructor of the class.
     * 
     * @return TRUE
     */
     
    final public function __destruct()
    {
        $reflect = new ReflectionClass(DATABASE);
        $database = $reflect->getName();
        $database::Disconnect_Host();
        Debugger::Dump_Constants(array ($this));
        
        return TRUE;   
    }
}

/**
 * @package Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */

abstract class Bootstrap
{
    const CONFIG_DIR = 'config';
    const CONSTANTS_FILE = 'constants';
    const INI_EXT = 'ini.php';
    const BOOTSTRAP_FILE = 'bootstrap';
     
    /**
     * Bootstrap::Set_Error_Reporting()
     * 
     * sets the error reporting level.
     * 
     * @param string $errors_file
     * @param bool $dev_mode
     * @return TRUE
     */
     
    final public static function Set_Error_Reporting
    (
        $errors_file = NULL,
        $dev_mode = DEV_MODE
    )
    {
        if ( !$errors_file )
        {
            $errors_file = Adjustment::Chain_Path
            (
                array
                (
                    ROOT,
                    TEMP_DIR,
                    LOGS_DIR,
                    Adjustment::Chain_File
                    (
                        array (ERRORS_FILE, LOG_EXT)
                    )
                )
            );
        }
        
        error_reporting(E_ALL);
        
        if ( $dev_mode === TRUE )
        {
            ini_set('display_errors', 'On');
        }
        else
        {
            $create_files = File_System::Create_Files
            (
                array ($errors_file)
            );
            
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', $errors_file);
        }
               
        return TRUE;    
    }
    
    /**
     * Bootstrap::Remove_Magic_Quotes()
     * 
     * removes magic quotes.
     * 
     * @param mixed $args
     * @return TRUE
     */
     
    final public static function Remove_Magic_Quotes
    (
        $args = array('_COOKIE', '_GET', '_POST')
    )
    {        
        if ( get_magic_quotes_gpc() )
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
        
        return TRUE;         
    }
    
    /**
     * Bootstrap::Unregister_Globals()
     * 
     * unsets globals for security reasons.
     * 
     * @param array $exceptions
     * @return TRUE
     */
     
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
        
        return TRUE;    
    }
    
    /**
     * Bootstrap::Call_Hook()
     * 
     * calls the hook of the project.
     * 
     * @return TRUE
     */
     
    final public static function Call_Hook()
    {
        return TRUE;
    }   
}

/**#@-*/

set_exception_handler('Debugger::Handle_Exception');

/**
 * Constant is located outside the class {@link Project} context because
 * its content is used later as instance name of the project object.
 * 
 * @uses File_System::Get_Project_Name()
 */

define('PROJECT_NAME', File_System::Get_Project_Name());

${PROJECT_NAME} = new Project;