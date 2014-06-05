<?php
abstract class Delivery_Agent
{
    final public static function Get_Array_Values($array, $keys = array ())
    {
        if ( empty($keys) )
        {
            $keys = array_keys($array);
        }
        
        $values = array ();
        
        foreach ( (array)$keys AS $id => $key )
        {
            if ( array_key_exists($key, $array) )
            {
                $values[$key] = $array[$key];
            }
            else
            {
                $message = Message_Stock::E_NO_ARR_KEY;
                    
                throw new Exception(vsprintf($message, array ($key)));      
            }    
        }
        
        $n = count($values);
        
        $array_values = ( $n <= 1 )
            ? $values[0]
            : $values;

        return $array_values;         
    }
}