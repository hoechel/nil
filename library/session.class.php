<?php
abstract class Session
{
    final public static function Start_Session()
    {
        $start_session = session_start();
        
        return $start_session;    
    }

    final public static function Look_Up_Session()
    {
        $look_up_session = ( defined('SID') )
            ? TRUE
            : FALSE;
        
        return $look_up_session;     
    }
     
    final public static function Get_Session_Vars($keys = FALSE)
    {   
        
        $values = Delivery_Agent::Get_Array_Values($_SESSION, $keys);
        
        return $values;
    }
     
    final public static function Save_Project_Constants
    (
        $args = array ('Project', 'Message_Stock')
    )
    {
        $constants = array();
        
        foreach( $args AS $class )
        {
            $reflect = new ReflectionClass($class);
            $class_constants = $reflect->getConstants();
            $constants = array_merge_recursive
            (
                $constants,
                $class_constants
            );   
        }
        
        $defined_constants = get_defined_constants(TRUE);
        $constants = array_merge_recursive
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
}