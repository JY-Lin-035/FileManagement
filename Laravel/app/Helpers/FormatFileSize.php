<?php

namespace App\Helpers;

class FormatFileSize
{
    public static function formatFileSize(int $bytes, string $Type = null): array
    {
        if ($Type) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;
            while ($units[$i] != $Type && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return [round($bytes, 2), null];
        }
        else {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            return [round($bytes, 2), $units[$i]];
        }
    }
}
