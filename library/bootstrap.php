<?php

if (!($require_shared_file = self::Require_Files(SHARED_FILENAME)))
{
    $file_extension = (defined('FILE_EXTENSION'))
        ? FILE_EXTENSION
        : self::DEFAULT_FILE_EXTENSION;
            
    throw new Exception
    (
        'Requiring file ' .
        SHARED_FILENAME . '.' . $file_extension .
        ' failed.'
    );        
}

$call_hook = self::Call_Hook();