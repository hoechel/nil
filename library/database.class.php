<?php
final class Database
extends mySQL
{
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
                    self::DB_CONNECT,
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
            
            throw new Exception
            (
                vsprintf($message, array ($db_user, $db_host))
            );
        }
       
        return TRUE;    
    }
    
    final private static function Create_Database($db_name = DATABASE_NAME)
    {
        if
        (
            !implode
            (
                array_map
                (
                    self::DB_QUERY,
                    array
                    (
                        vsprintf(self::CREATE_DB_QUERY, array ($db_name))
                    )
                )
            ) 
        )
        {
            $message = Message_Stock::E_DB_CREATION;
            $exception = new Exception
            (
                vsprintf($message, array ($db_name))
            );
            
            Debugger::Catch_Exception($exception);    
        }
        
        return TRUE;        
    }
    
    final private static function Use_Database($db_name = DATABASE_NAME)
    {
        self::Create_Database($db_name);
        
        if ( !implode(array_map(self::DB_SELECT, array ($db_name))) )
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
    
    final private static function Create_Table($table, $query)
    {
        if
        (   !implode
            (
                array_map
                (
                    self::DB_QUERY,
                    array
                    (
                        vsprintf
                        (
                            self::CREATE_TABLE_QUERY,
                            array ($table, $query)
                        )
                    )
                )
            )
        )
        {
            $message = Message_Stock::E_DB_SYNTAX_ERR;
            $exception = new Exception($message);
            
            Debugger::Catch_Exception($exception);    
        }

        return TRUE;    
    }
    
    final private static function Select_Data_Records($query)
    {
        $handle = array_map(self::DB_QUERY, array ($query));
        
        if
        (
            implode($handle)
        )
        {
            $results = array ();
            $n = 0;
           
            while
            (
                $row = array_map
                (
                    self::DB_FETCH_ASSOC, array ($handle[0])
                ) AND
                $row[0]
            )
            {
                $results[$n++] = $row[0];
            }
        }
        else
        {
            $message = Message_Stock::E_DB_SYNTAX_ERR;
            $exception = new Exception($message);
            
            Debugger::Catch_Exception($exception);     
        }
        
        $data_records = ( $n == 1 )
            ? $results[0]
            : $results;
            
        return $data_records;
    }
    
    final private static function Insert_Data_Records($query)
    {
       if ( !implode(array_map(self::DB_QUERY, array ($query))) )
       {
            $message = Message_Stock::E_DB_SYNTAX_ERR;
            $exception = new Exception($message);
            
            Debugger::Catch_Exception($exception);     
       };
    }
    
    final public static function Save_Project_Vars($args = array ())
    {
        
        if ( self::Look_up_Database() AND self::Use_Database() )
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
                self::SELECT_QUERY,
                array
                (
                    self::SELECT_ALL_EXPR,
                    TABLE_SESSIONS
                )
            ) .
            vsprintf
            (
                self::WHERE_CLAUSE,
                array
                (
                    TABLE_SESSIONS_SESSION_ID,
                    Session::Get_Session_Id()
                )
            );
            $sessions_columns = self::Select_Data_Records
            (
                vsprintf(self::SHOW_COLUMNS_QUERY, array (TABLE_SESSIONS))
            );
            $colname_expr = NULL;
            $n = count($sessions_columns);
            
            for ( $i = 0; $i < $n; $i++ )
            {
                foreach ( $sessions_columns[$i] AS $key => $value )
                { 
                    if ( $key == self::DB_FIELD )
                    {
                        $colname_expr .= vsprintf
                        (
                            self::INSERT_COLNAME_EXPR,
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
                self::INSERT_QUERY,
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
                    self::SHOW_COLUMNS_QUERY,
                    array (TABLE_PROJECT_VARS)
                )
            );
            $colname_expr = NULL;
            $n = count($project_columns);
            
            for ( $i = 0; $i < $n; $i++ )
            {
                foreach ( $project_columns[$i] AS $key => $value )
                {
                    
                    if ( $key == self::DB_FIELD )
                    {
                        $colname_expr .= vsprintf
                        (
                            self::INSERT_COLNAME_EXPR,
                            array ($value)
                        );
                    }
                }    
            }
            
            $colname_expr = substr($colname_expr, 0, -2);
            $insert_values_expr = NULL;
            
            foreach
            (
                SESSION::Get_Session_Vars() AS $const_key => $const_val
            )
            {
                $select_query = vsprintf
                (
                    self::SELECT_QUERY,
                    array (self::SELECT_ALL_EXPR, TABLE_PROJECT_VARS)
                ) .
                vsprintf
                (
                    self::WHERE_CLAUSE,
                    array (TABLE_PROJECT_VARS_SESSION_ID, $session['id'])
                ) .
                vsprintf
                (
                    self::WHERE_AND_CLAUSE,
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
                    self::INSERT_QUERY,
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
        
    final public static function Disconnect_Host()
    {
        array_map(self::DB_DISCONNECT, array ());
        
        return TRUE;
    }
}