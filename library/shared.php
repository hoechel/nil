<?php
if (!($magic_quotes = self::Remove_Magic_Quotes()))
{
    throw new Exception('Can not remove magic quotes.');
}

if (!($unregister_globals = self::Unregister_Globals()))
{
    throw new Exception('Can not unregister globals.');
} 

if (!($set_error_reporting = self::Set_Error_Reporting()))
{
    throw new Exception('Can not set error reporting.');
}

if (!($set_error_reporting = self::Set_Error_Reporting()))
{
    throw new Exception('Can not set error reporting.');
}