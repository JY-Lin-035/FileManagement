<?php

namespace App\Services;

use App\Models\Accounts;
use App\Helpers\FormatFileSize;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use RecursiveDirectoryIterator;

use Exception;

class FolderService
{
    public static function totalUsedSize($userID, $dir = '/Home')
    {
        $totalSize = 0;

        $files = Storage::disk('private')->allFiles('users/' . $userID . $dir);
        foreach ($files as $path) {
            $totalSize += Storage::disk('private')->size($path);
        }

        return $totalSize;
    }

    public static function getContent($userID, $folder)
    {
        $folder = strtr($folder, '-_', '+/');
        $fileList = [];
        $path = storage_path('app/private/users/' . $userID . '/' .  str_replace('-', '/', base64_decode($folder)));
        $realPath = realpath($path);

        if ($realPath === false || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home')) {
            Log::info($realPath);
            return $fileList;
        }

        foreach (new RecursiveDirectoryIterator($realPath, RecursiveDirectoryIterator::SKIP_DOTS) as $file) {
            if ($file->isFile()) {
                $size = FormatFileSize::formatFileSize($file->getSize());
                $size = $size[0] . ' ' . $size[1];
                $fileList[] = ['type' => 'file', 'name' => $file->getFilename(), 'size' => $size, 'date' => date('Y-m-d H:i:s', $file->getMTime() + (8 * 60 * 60))];
            } else if ($file->isDir()) {
                $fileList[] = ['type' => 'folder', 'name' => $file->getFilename(), 'size' => '-', 'date' => date('Y-m-d H:i:s', $file->getMTime() + (8 * 60 * 60))];
            }
        }

        return $fileList;
    }

    public static function create($userID, $dir, $newName)
    {
        try {
            $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);;
            $realPath = realpath($path);
            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
                !preg_match('/^[A-Za-z0-9\p{Han}]{1,30}$/u', $newName)
            ) {
                return [null, 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/';
            if (Storage::disk('private')->exists($endPath . $newName)) {
                return [null, 403];
            }

            Storage::disk('private')->makeDirectory($endPath . $newName);
            return [date('Y-m-d H:i:s', Storage::disk('private')->lastModified($endPath . $newName) + (8 * 60 * 60)), 200];
        } catch (Exception $e) {
            return [null, 403];
        }
    }

    public static function rename($userID, $dir, $originName, $newName)
    {
        try {
            $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);
            $fullPath = $path . '/' . $originName;
            $realPath = realpath($fullPath);
            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
                !preg_match('/^[A-Za-z0-9\p{Han}]{1,30}$/u', $originName) ||
                !preg_match('/^[A-Za-z0-9\p{Han}]{1,30}$/u', $newName)
            ) {
                return [null, 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/';
            if (Storage::disk('private')->exists($endPath . $originName)) {
                Storage::disk('private')->move($endPath . $originName, $endPath . $newName);
                return [null, 200];
            }
        } catch (Exception $e) {
            return [null, 403];
        }
    }

    public static function delete($userID, $dir, $folderName)
    {
        try {
            $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);;
            $realPath = realpath($path);
            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\')
            ) {
                return [null, 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/' . $folderName;
            if (Storage::disk('private')->exists($endPath)) {
                $size = FolderService::totalUsedSize($userID, '/' . $dir . '/' . $folderName);
                Storage::disk('private')->deleteDirectory($endPath);
                $account = Accounts::find($userID);
                $account->usedSize -= $size;

                return [['size' => $size], 200];
            }
            return [null, 403];
        } catch (Exception $e) {
            return [null, 403];
        }
    }
}
