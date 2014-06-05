<?php
/**
 * NIL /library/delivery_agent.class.php
 * 
 * abstracts delivery agent class.
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
 * Delivery_Agent
 * 
 * handles delivery agent requests.
 * 
 * @package NIL Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Delivery_Agent
{
    /**
     * Delivery_Agent::Get_Class_Constants()
     * 
     * @param array $args
     * @return array
     */
     
    final public static function Get_Class_Constants(array $args)
    {
        $class_constants = array ();
        
        foreach( $args AS $class )
        {
            $reflect = new ReflectionClass($class);
            $constants = $reflect->getConstants();
            $class_constants = array_replace_recursive
            (
                $class_constants,
                $constants
            );   
        }
        
        return $class_constants;
    }
    
    /**
     * Delivery_Agent::Get_Array_Values()
     * 
     * returns values of given keys of an array.
     * 
     * @param array $array
     * @param array $keys
     * @return mixed
     */
     
    final public static function Get_Array_Values(array $array, array $keys = NULL)
    {
        if ( empty($keys) )
        {
            $keys = array_keys($array);
        }
        
        $values = array ();
        
        foreach ( $keys AS $id => $key )
        {
            if ( array_key_exists($key, $array) )
            {
                $values[$key] = $array[$key];
            }
            else
            {
                $message = vsprintf
                (
                    Message_Stock::E_NO_ARR_KEY,
                    array ($key)
                );
                    
                throw new Exception($message);      
            }    
        }
        
        $n = count($values);
        
        $array_values = ( $n <= 1 )
            ? $values[0]
            : $values;

        return $array_values;         
    }
}