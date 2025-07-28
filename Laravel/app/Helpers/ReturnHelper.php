<?php

namespace App\Helpers;

class ReturnHelper
{
    public static function controllerReturn(array $result, $successReturn)
    {
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['stateCode']);
        } else {
            return $successReturn;
        }
    }
}
