<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

use Exception;

class LogHelper
{
    public static function errLog(string $title, Exception $err, string $level = 'debug')
    {
        Log::{$level}($title, [
            'msg' => $err->getMessage(),
            'file' => $err->getFile(),
            'line' => $err->getLine(),
        ]);
    }
}
