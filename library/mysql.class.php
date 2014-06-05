<?php
abstract class mySQL
{
    const DB_CONNECT = 'mysql_connect';
    const DB_QUERY = 'mysql_query';
    const DB_SELECT = 'mysql_select_db';
    const DB_DISCONNECT = 'mysql_close';
    const DB_FETCH_ASSOC = 'mysql_fetch_assoc';
    const DB_FIELD = 'Field';
    const CREATE_DB_QUERY = 'CREATE DATABASE IF NOT EXISTS `%s`';
    const CREATE_TABLE_QUERY = 'CREATE TABLE IF NOT EXISTS `%s` (%s)';
    const SELECT_QUERY = 'SELECT %s FROM `%s`';
    const SELECT_ALL_EXPR = '*';
    const SHOW_COLUMNS_QUERY = 'SHOW COLUMNS FROM `%s`';
    const WHERE_CLAUSE = ' WHERE `%s` = "%s"';
    const WHERE_AND_CLAUSE = ' AND `%s` = "%s"';
    const INSERT_QUERY = 'INSERT INTO `%s` (%s) VALUES %s';
    const INSERT_COLNAME_EXPR = '`%s`, ';
    const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    
    final public static function Look_Up_Database()
    {
        $look_up_database = mysql_ping();

        return $look_up_database;
    }
}