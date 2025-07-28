<?php

namespace App\Services;

use App\Models\Accounts;
use App\Helpers\LogHelper;
use App\Helpers\FormatFileSize;

use Illuminate\Support\Facades\Storage;

use RecursiveDirectoryIterator;

use Exception;

class FolderService
{
    public static function totalUsedSize(string $userID, string $dir = '/Home')
    {
        $totalSize = 0;

        $files = Storage::disk('private')->allFiles('users/' . $userID . $dir);
        foreach ($files as $path) {
            $totalSize += Storage::disk('private')->size($path);
        }

        return $totalSize;
    }

    public function getContent(string $userID, string $folder, bool $debug = false)
    {
        try {
            $folder = strtr($folder, '-_', '+/');
            $fileList = [];
            $path = storage_path('app/private/users/' . $userID . '/' .  str_replace('-', '/', base64_decode($folder)));
            $realPath = realpath($path);

            if ($realPath === false || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home')) {
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

            return ['fileList' => $fileList, 'stateCode' => 200];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FolderService-getContent: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function create(string $userID, string $dir, string $newName, bool $debug = false)
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
                return ['error' => 'Error', 'stateCode' => 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/';
            if (Storage::disk('private')->exists($endPath . $newName)) {
                return ['error' => 'Error', 'stateCode' => 403];
            }

            Storage::disk('private')->makeDirectory($endPath . $newName);
            return ['date' => date('Y-m-d H:i:s', Storage::disk('private')->lastModified($endPath . $newName) + (8 * 60 * 60)), 'stateCode' => 200];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FolderService-create: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function rename(string $userID, string $dir, string $originName, string $newName, bool $debug = false)
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
                return ['error' => 'Error', 'stateCode' => 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/';
            if (Storage::disk('private')->exists($endPath . $originName)) {
                Storage::disk('private')->move($endPath . $originName, $endPath . $newName);
                return ['msg' => 'success', 'stateCode' => 200];
            }
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FolderService-rename: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function delete(string $userID, string $dir, string $folderName, bool $debug = false)
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
                return ['error' => 'Error', 'stateCode' => 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/' . $folderName;
            if (Storage::disk('private')->exists($endPath)) {
                $size = FolderService::totalUsedSize($userID, '/' . $dir . '/' . $folderName);
                Storage::disk('private')->deleteDirectory($endPath);
                $account = Accounts::find($userID);
                $account->usedSize -= $size;

                return [['size' => $size], 200];
                return ['size' => $size, 'stateCode' => 200];
            }

            return ['error' => 'Error', 'stateCode' => 403];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FolderService-delete: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }
}
