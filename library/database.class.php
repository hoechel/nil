<?php
/**
 * NIL /library/database.class.php
 * 
 * abstracts database class.
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package NIL Core
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel
 * @license GPL <http://opensource.org/licenses/GPL-3.0>
 * @version 0.3
 */

/**
 * Database
 * 
 * handles database request.
 * 
 * @package NIL Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class Database
{
    /**
     * Database::Connect_Host()
     * 
     * connects database hosts.
     * 
     * @param mixed $db_host
     * @param mixed $db_user
     * @param mixed $db_pwd
     * @return
     */
     
    final public static function Connect_Host
    (
        $db_host = DATABASE_HOST,
        $db_user = DATABASE_USER,
        $db_pwd = DATABASE_PWD
    )
    {
        if
        (
            !implode
            (
                @array_map
                (
                    static::DB_CONNECT,
                    array ($db_host),
                    array ($db_user),
                    array ($db_pwd)
                )
            )
        )
        {
            $message = ( $db_pwd )
                ? Message_Stock::E_NO_DB_COND_YES
                : Message_Stock::E_NO_DB_COND_NO;
            
            $message = vsprintf($message, array ($db_user, $db_host));
            
            throw new Exception($message);
        }
       
        return TRUE;    
    }
    
    /**
     * Database::Store_Project_Vars()
     * 
     * stores project vars into project's project_vars table.
     * 
     * @return TRUE
     */
     
    final public static function Store_Project_Vars()
    { 
        if ( static::Look_up_Database() AND self::Select_Database() )
        {
            $create_table_sessions = vsprintf
            (
                TABLE_SESSIONS_COLUMN_DEF,
                array
                (
                    TABLE_SESSIONS_ID,
                    TABLE_SESSIONS_SESSION_ID,
                    TABLE_SESSIONS_CREATED
                )
            );
            
            $create_table_project = vsprintf
            (
                TABLE_PROJECT_VARS_COLUMN_DEF,
                array
                (
                    TABLE_PROJECT_VARS_ID,
                    TABLE_PROJECT_VARS_SESSION_ID,
                    TABLE_PROJECT_VARS_CONST_KEY,
                    TABLE_PROJECT_VARS_CONST_VAL
                )
            );
            
            self::Create_Table(TABLE_SESSIONS, $create_table_sessions);
            self::Create_Table(TABLE_PROJECT_VARS, $create_table_project);
            
            $select_session = vsprintf
            (
                static::SELECT_QUERY,
                array
                (
                    static::SELECT_ALL_EXPR,
                    TABLE_SESSIONS
                )
            ) .
            vsprintf
            (
                static::WHERE_CLAUSE,
                array
                (
                    TABLE_SESSIONS_SESSION_ID,
                    Session::Get_Session_Id()
                )
            );
            
            $sessions_columns = self::Select_Data_Records
            (
                vsprintf(static::SHOW_COLUMNS_QUERY, array (TABLE_SESSIONS))
            );
            
            $colname_expr = NULL;
            $n = count($sessions_columns);
            
            for ( $i = 0; $i < $n; $i++ )
            {
                foreach ( $sessions_columns[$i] AS $key => $value )
                { 
                    if ( $key == static::DB_FIELD )
                    {
                        $colname_expr .= vsprintf
                        (
                            static::INSERT_COLNAME_EXPR,
                            array ($value)
                        );
                    }
                }    
            }
            
            $colname_expr = substr($colname_expr, 0, -2);
            $values_expr = substr
            (
                vsprintf
                (
                    TABLE_SESSIONS_INSERT_EXPR,
                    array (Session::Get_Session_Id())
                ),
                0,
                -2
            );
            
            $insert_query = vsprintf
            (
                static::INSERT_QUERY,
                array
                (
                    TABLE_SESSIONS,
                    $colname_expr,
                    $values_expr
                )
            );

            if( !$session = self::Select_Data_Records($select_session) )
            {
                self::Insert_Data_Records($insert_query);
                
                $session = self::Select_Data_Records($select_session);
            }
            
            $project_columns = self::Select_Data_Records
            (
                vsprintf
                (
                    static::SHOW_COLUMNS_QUERY,
                    array (TABLE_PROJECT_VARS)
                )
            );
            
            $colname_expr = NULL;
            $n = count($project_columns);
            
            for ( $i = 0; $i < $n; $i++ )
            {
                foreach ( $project_columns[$i] AS $key => $value )
                {
                    
                    if ( $key == static::DB_FIELD )
                    {
                        $colname_expr .= vsprintf
                        (
                            static::INSERT_COLNAME_EXPR,
                            array ($value)
                        );
                    }
                }    
            }
            
            $colname_expr = substr($colname_expr, 0, -2);
            $insert_values_expr = NULL;
            $session_vars = SESSION::Get_Session_Vars();
            
            foreach ( $session_vars AS $const_key => $const_val )
            {
                $select_query = vsprintf
                (
                    static::SELECT_QUERY,
                    array (static::SELECT_ALL_EXPR, TABLE_PROJECT_VARS)
                ) .
                vsprintf
                (
                    static::WHERE_CLAUSE,
                    array (TABLE_PROJECT_VARS_SESSION_ID, $session['id'])
                ) .
                vsprintf
                (
                    static::WHERE_AND_CLAUSE,
                    array (TABLE_PROJECT_VARS_CONST_KEY, $const_key)
                );
                
                if ( !self::Select_Data_Records($select_query) )
                {
                    $insert_values_expr .= vsprintf
                    (
                        TABLE_PROJECT_VARS_INSERT_EXPR,
                        array
                        (
                            $session['id'],
                            $const_key,
                            addslashes($const_val)
                        )
                    );    
                }
            }
            
            if ( $insert_values_expr )
            {
                $insert_values_expr = substr($insert_values_expr, 0, -2);
                
                $insert_query = vsprintf
                (
                    static::INSERT_QUERY,
                    array
                    (
                        TABLE_PROJECT_VARS,
                        $colname_expr,
                        $insert_values_expr
                    )
                );
                
                self::Insert_Data_Records($insert_query);    
            }  
        }
        
        return TRUE;
    }
    
    /**
     * Database::Select_Database()
     * 
     * selects databases.
     * 
     * @param mixed $db_name
     * @return
     */
     
    final private static function Select_Database($db_name = DATABASE_NAME)
    {
        self::Create_Database($db_name);
        
        if ( !implode(array_map(static::DB_SELECT, array ($db_name))) )
        {
            $message = Message_Stock::E_DB_SELECTION;
            $exception = new Exception
            (
                vsprintf($message, array ($db_name))
            );
            
            Debugger::Catch_Exception($exception);      
        }
        
        return TRUE;      
    }
    
    /**
     * Database::Create_Database()
     * 
     * creates databases.
     * 
     * @param string $db_name
     * @return TRUE
     */
     
    final private static function Create_Database($db_name = DATABASE_NAME)
    {
        if
        (
            !implode
            (
                array_map
                (
                    static::DB_QUERY,
                    array
                    (
                        vsprintf(static::CREATE_DB_QUERY, array ($db_name))
                    )
                )
            ) 
        )
        {
            $message = vsprintf
            (
                Message_Stock::E_DB_CREATION,
                array ($db_name)
            );
            
            throw new Exception($message);
             
        }
        
        return TRUE;        
    }
    
    /**
     * Database::Create_Table()
     * 
     * creates database tables.
     * 
     * @param string $table
     * @param string $query
     * @return TRUE
     */
     
    final private static function Create_Table($table, $query)
    {
        $create_table_query = vsprintf
        (
            static::CREATE_TABLE_QUERY,
            array ($table, $query)
        );
        
        if
        (   !implode
            (
                array_map(static::DB_QUERY, array ($create_table_query))
            )
        )
        {
            $message = vsprintf
            (
                Message_Stock::E_DB_SYNTAX_ERR,
                array ($create_table_query)
            );
            
            throw new Exception($message);   
        }

        return TRUE;    
    }
    
    /**
     * Database::Select_Data_Records()
     * 
     * gets data records from database.
     * 
     * @param string $query
     * @return string|array
     */
     
    final private static function Select_Data_Records($query)
    {
        $handle = array_map(static::DB_QUERY, array ($query));
        
        if ( implode($handle) )
        {
            $results = array ();
            $n = 0;
           
            while
            (
                $row = array_map
                (
                    static::DB_FETCH_ASSOC, array ($handle[0])
                ) AND
                $row[0]
            )
            {
                $results[$n++] = $row[0];
            }
        }
        else
        {
            $message = vsprintf
            (
                Message_Stock::E_DB_SYNTAX_ERR,
                array ($query)
            );
            
            throw new Exception($message);    
        }
        
        $data_records = ( $n == 1 )
            ? $results[0]
            : $results;
            
        return $data_records;
    }
    
    /**
     * Database::Insert_Data_Records()
     * 
     * inserts data records into database.
     * 
     * @param string $query
     * @return TRUE
     */
     
    final private static function Insert_Data_Records($query)
    {
       if ( !implode(array_map(static::DB_QUERY, array ($query))) )
       {
            $message = vsprintf
            (
                Message_Stock::E_DB_SYNTAX_ERR,
                array ($query)
            );
            
            throw new Exception($message);     
       };
       
       return TRUE;
    }
    
    /**
     * Database::Disconnect_Host()
     * 
     * disconnects from database hosts.
     * 
     * @return TRUE
     */
     
    final public static function Disconnect_Host()
    {
        array_map(static::DB_DISCONNECT, array ());
        
        return TRUE;
    }
}