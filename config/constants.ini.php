<?php
/**
 * NIL /config/constants.ini.php
 * 
 * defines core constants of the program.
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

define('DEV_MODE', FALSE);
define('CONFIG_DIR', 'config');
define('LIBRARY_DIR', 'library');
define('BOOTSTRAP_FILE', 'bootstrap');
define('INI_EXT', 'ini.php');
define('PHP_EXT', 'php');
define('TEMP_DIR', 'temp');
define('LOGS_DIR', 'logs');
define('ERRORS_FILE', 'errors');
define('LOG_EXT', 'log');
define('CLASS_EXT', 'class.php');
define('DATABASE', 'mySQL');
define('DATABASE_HOST', 'localhost');
define('DATABASE_USER', 'root');
define('DATABASE_PWD', '');
define('DATABASE_NAME', PROJECT_NAME);
define('TABLE_SESSIONS', 'sessions');
define
(
    'TABLE_SESSIONS_COLUMN_DEF',
    '`%1$s` int(11) NOT NULL AUTO_INCREMENT,' .
    '`%2$s` varchar(255) NOT NULL,' .
    '`%3$s` timestamp DEFAULT CURRENT_TIMESTAMP,' .
    'PRIMARY KEY (`%1$s`)'
);
define('TABLE_SESSIONS_ID', 'id');
define('TABLE_SESSIONS_SESSION_ID', 'session_id');
define('TABLE_SESSIONS_CREATED', 'created');
define
(
    'TABLE_SESSIONS_INSERT_EXPR',
    '("", "%1$s", CURRENT_TIMESTAMP), '
);
define('TABLE_PROJECT_VARS', 'project_vars');
define
(
    'TABLE_PROJECT_VARS_COLUMN_DEF',
    '`%1$s` int(11) NOT NULL AUTO_INCREMENT,' .
    '`%2$s` int(11) NOT NULL,' .
    '`%3$s` varchar(255) NOT NULL,' .
    '`%4$s` varchar(255) NOT NULL,' .
    'PRIMARY KEY (`%1$s`)'
);
define('TABLE_PROJECT_VARS_ID', 'id');
define('TABLE_PROJECT_VARS_SESSION_ID', 'session_id');
define('TABLE_PROJECT_VARS_CONST_KEY', 'const_key');
define('TABLE_PROJECT_VARS_CONST_VAL', 'const_val');
define
(
    'TABLE_PROJECT_VARS_INSERT_EXPR',
    '("", "%1$s","%2$s", "%3$s"), '
);