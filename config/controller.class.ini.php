<?php
final class {PROJECT_NAME}_Controller
{
    public function __construct()
    {
        if ( array_key_exists('PATH_INFO', $_SERVER) )
        {
            $url = explode
            (
                '\\',
                Adjustment::Level_Out_Path($_SERVER['PATH_INFO'])
            );
            $controller = $url[0];
            
            $view = ( isset($url[1]) AND $url[1] )
                ? $url[1]
                : NULL;    
        }
    }
}