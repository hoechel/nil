<?php
/**
 * NIL /library/session.class.php
 * 
 * abstracts session class.
 * 
 * @todo long description
 * 
 * This program is free software: you can redistribute it and/or
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
 * @package NIL Core
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel
 * @license GPL <http://opensource.org/licenses/GPL-3.0>
 * @version 0.3
 */
 
/**
 * Session
 * 
 * handles session requests.
 * 
 * @package NIL Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Session
{
    /**
     * Session::Start_Session()
     * 
     * starts sessions.
     * 
     * @return TRUE
     */
     
    final public static function Start_Session($session_name = NULL)
    {
        if ( $session_name )
        {
            session_name($session_name);    
        }
        
        if ( !($start_session = session_start()) )
        {
            $message = Message_Stock::E_SESS_NOT_STARTED;
            
            throw new Exception($message);    
        }
        
        return TRUE;    
    }
    
    /**
     * Session::Store_Project_Constants()
     * 
     * gets all project's constants and stores them into session.
     * 
     * @param array $args
     * @return TRUE
     */
     
    final public static function Store_Project_Constants
    (
        $args = array ('Project', 'Message_Stock')
    )
    {
        $constants = Delivery_Agent::Get_Class_Constants($args);
        $defined_constants = get_defined_constants(TRUE);
        $constants = array_replace_recursive
        (
            $constants,
            $defined_constants['user']
        );

        if ( self::Look_Up_Session() )
        {
            foreach ( $constants AS $key => $value )
            {
                $_SESSION[$key] = $value;
            }
        }
        else
        {
            $message = Message_Stock::E_NO_SESS;
                
            throw new Exception($message);     
        }
        
        return TRUE;   
    }
    
    /**
     * Session::Look_Up_Session()
     * 
     * tests if session id exists.
     * 
     * @return string
     */
     
    final public static function Look_Up_Session()
    {
        $look_up_session = ( defined('SID') )
            ? TRUE
            : FALSE;
        
        return $look_up_session;     
    }
    
    /**
     * Session::Get_Session_Id()
     * 
     * returns session id.
     * 
     * @return string
     */
     
    final public static function Get_Session_Id()
    {
        if ( self::Look_Up_Session() )
        {
            $session_id = session_id();
        }
        else
        {
            $message = Message_Stock::E_NO_SESS;
                
            throw new Exception($message);    
        }
        
        return $session_id;
    }

    /**
     * Session::Get_Session_Vars()
     * 
     * gets session vars on given keys.
     * 
     * @param array $keys
     * @return array
     */
     
    final public static function Get_Session_Vars(array $keys = NULL)
    {   
        $values = Delivery_Agent::Get_Array_Values($_SESSION, $keys);
        
        return $values;
    }     
}