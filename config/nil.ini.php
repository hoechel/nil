<?php
define('DEV_MODE', TRUE);
define('APPLICATION_DIR', 'application');
define('CONTROLLERS_DIR', 'controllers');
define('MODELS_DIR', 'models');
define('PUBLIC_DIR', 'public');
define('TEMP_DIR', 'temp');
define('LOGS_DIR', 'logs');
define('ERRORS_FILE', 'errors');
define('EXCEPTIONS_FILE', 'exceptions');
define('CLASS_EXT', 'class.php');
define('LOG_EXT', 'log');
define('CONTROLLER_SUFFIX', '_controller');
define('DATABASE_SERVER', 'mySQL');
define('DATABASE_HOST', 'localhost');
define('DATABASE_USER', 'root');
define('DATABASE_PWD', '');
define('DATABASE_NAME', 'Nil');
define('TABLE_SESSIONS', 'sessions');
define('TABLE_SESSIONS_ID', 'id');
define('TABLE_SESSIONS_SESSION_ID', 'session_id');
define('TABLE_SESSIONS_CREATED', 'created');
define
(
    'TABLE_SESSIONS_COLUMN_DEF',
    '`%1$s` int(11) NOT NULL AUTO_INCREMENT,' .
    '`%2$s` varchar(255) NOT NULL,' .
    '`%3$s` timestamp DEFAULT CURRENT_TIMESTAMP,' .
    'PRIMARY KEY (`%1$s`)'
);
define
(
    'TABLE_SESSIONS_INSERT_EXPR',
    '("", "%1$s", CURRENT_TIMESTAMP), '
);
define('TABLE_PROJECT_VARS', 'project_vars');
define('TABLE_PROJECT_VARS_ID', 'id');
define('TABLE_PROJECT_VARS_SESSION_ID', 'session_id');
define('TABLE_PROJECT_VARS_CONST_KEY', 'const_key');
define('TABLE_PROJECT_VARS_CONST_VAL', 'const_val');
define
(
    'TABLE_PROJECT_VARS_COLUMN_DEF',
    '`%1$s` int(11) NOT NULL AUTO_INCREMENT,' .
    '`%2$s` int(11) NOT NULL,' .
    '`%3$s` varchar(255) NOT NULL,' .
    '`%4$s` varchar(255) NOT NULL,' .
    'PRIMARY KEY (`%1$s`)'
);
define
(
    'TABLE_PROJECT_VARS_INSERT_EXPR',
    '("", "%1$s","%2$s", "%3$s"), '
);
?>