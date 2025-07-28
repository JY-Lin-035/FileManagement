<?php

namespace App\Services;

use App\Models\Accounts;
use App\Helpers\LogHelper;

use Illuminate\Support\Facades\Storage;

use Exception;

class FileService
{
    public function download(string $userID, string $dir, string $fileName, bool $debug = false)
    {
        try {
            $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);
            $fullPath = $path . '/' . $fileName;
            $realPath = realpath($fullPath);

            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
                str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
                str_contains($fileName, '../') || str_contains($fileName, '..\\') ||
                str_contains($fileName, './') || str_contains($fileName, '.\\') ||
                str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
                str_starts_with($fileName, '.')
            ) {
                return ['error' => 'NotFound', 'stateCode' => 404];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/';
            if (Storage::disk('private')->exists($endPath . $fileName)) {
                return ['realPath' => $realPath, 'fileName' => $fileName];
            }

            return ['error' => 'NotFound', 'stateCode' => 404];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FileService-download: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function delete(string $userID, string $dir, string $fileName, bool $debug = false)
    {
        try {
            $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);
            $fullPath = $path . '/' . $fileName;
            $realPath = realpath($fullPath);

            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
                str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
                str_contains($fileName, '../') || str_contains($fileName, '..\\') ||
                str_contains($fileName, './') || str_contains($fileName, '.\\') ||
                str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
                str_starts_with($fileName, '.')
            ) {
                return ['error' => 'NotFound', 'stateCode' => 404];
            }

            $endPath = 'users/' . $userID . '/' . $dir . '/' . $fileName;
            if (Storage::disk('private')->exists($endPath)) {
                $size = Storage::disk('private')->size($endPath);
                Storage::delete($endPath);

                return ['size' => $size, 'stateCode' => 200];
            }

            return ['error' => 'NotFound', 'stateCode' => 404];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FileService-delete: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function upload(string $userID, string $dir, $file, bool $debug = false)
    {
        try {
            $account = Accounts::find($userID);
            $dir = str_replace('-', '/', $dir);
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);
            $realPath = realpath($path);

            if (
                !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $userID . '\\' . 'Home') ||
                str_contains($dir, '../') || str_contains($dir, '..\\') ||
                str_contains($dir, './') || str_contains($dir, '.\\') ||
                str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
                str_starts_with($file->getClientOriginalName(), '/') || str_starts_with($file->getClientOriginalName(), '\\') ||
                str_contains($file->getClientOriginalName(), '../') || str_contains($file->getClientOriginalName(), '..\\') ||
                str_contains($file->getClientOriginalName(), './') || str_contains($file->getClientOriginalName(), '.\\') ||
                str_starts_with($file->getClientOriginalName(), '/') || str_starts_with($file->getClientOriginalName(), '\\') ||
                str_starts_with($file->getClientOriginalName(), '.') || $file->getSize() > $account->signalFileSize
            ) {
                return ['error' => 'Error', 'stateCode' => 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir;
            $fullPath = $endPath . '/' . $file->getClientOriginalName();

            if (Storage::disk('private')->exists($endPath)) {
                if (Storage::disk('private')->exists($fullPath) && Storage::disk('private')->size($fullPath) === $file->getSize()) {
                    return ['msg' => 'success', 'stateCode' => 200];
                }

                $file->storeAs($endPath, $file->getClientOriginalName(), 'private');

                $account->usedSize += $file->getSize();
                $account->save();

                return ['msg' => 'success', 'stateCode' => 200];
            }
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('FileService-upload: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }
}
