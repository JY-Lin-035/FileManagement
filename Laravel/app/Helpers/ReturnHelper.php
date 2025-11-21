<?php

namespace App\Helpers;

class ReturnHelper
{
    public static function controllerReturn(array $result, $data)
    {
        if (isset($result['error'])) {
            return ['data' => ['error' => $result['error']], 'stateCode' => $result['stateCode']];
        } else {
            return ['data' => $data, 'stateCode' => 200];
        }
    }
}
