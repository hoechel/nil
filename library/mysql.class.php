<?php
/**
 * NIL /library/mysql.class.php
 * 
 * abstracts mysql class.
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
 * mySQL
 * 
 * handles mySQL requests.
 * 
 * @package NIL Core  
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel 
 * @version 0.3
 * @access public
 */
 
abstract class mySQL
extends Database
{
    const DB_CONNECT = 'mysql_connect';
    const DB_QUERY = 'mysql_query';
    const CREATE_DB_QUERY = 'CREATE DATABASE IF NOT EXISTS `%s`';
    const DB_SELECT = 'mysql_select_db';
    const CREATE_TABLE_QUERY = 'CREATE TABLE IF NOT EXISTS `%s` (%s)';
    const SELECT_QUERY = 'SELECT %s FROM `%s`';
    const SELECT_ALL_EXPR = '*';
    const WHERE_CLAUSE = ' WHERE `%s` = "%s"';
    const SHOW_COLUMNS_QUERY = 'SHOW COLUMNS FROM `%s`';
    const DB_FETCH_ASSOC = 'mysql_fetch_assoc';
    const DB_FIELD = 'Field';
    const INSERT_COLNAME_EXPR = '`%s`, ';
    const INSERT_QUERY = 'INSERT INTO `%s` (%s) VALUES %s';
    const WHERE_AND_CLAUSE = ' AND `%s` = "%s"';
    const DB_DISCONNECT = 'mysql_close';
    
    /**
     * mySQL::Look_Up_Database()
     * 
     * tests if database server is connected.
     * 
     * @return bool
     */
     
    final public static function Look_Up_Database()
    {
        $look_up_database = mysql_ping();

        return $look_up_database;
    }   
}