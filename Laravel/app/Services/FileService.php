<?php

namespace App\Services;

use App\Models\Accounts;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Exception;

class FileService
{
    public static function download($userID, $dir, $fileName)
    {
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
            return 404;
        }

        $endPath = 'users/' . $userID . '/' . $dir . '/';
        if (Storage::disk('private')->exists($endPath . $fileName)) {
            // return Storage::download($endPath);
            return response()->download($realPath, $fileName);
        }

        return 404;
    }

    public static function delete($userID, $dir, $fileName)
    {
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
            return 404;
        }

        $endPath = 'users/' . $userID . '/' . $dir . '/' . $fileName;
        if (Storage::disk('private')->exists($endPath)) {
            $size = Storage::disk('private')->size($endPath);
            Storage::delete($endPath);
            return $size;
        }

        return 404;
    }

    public static function upload($userID, $dir, $file)
    {
        try {
            $account = Accounts::find($userID);
            $dir = str_replace('-', '/', $dir);
            $path = storage_path('app/private/users/' . $userID . '/' . $dir);
            $realPath = realpath($path);

            // Log::info($realPath);
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
                return [null, 403];
            }

            $endPath = 'users/' . $userID . '/' . $dir;
            $fullPath = $endPath . '/' . $file->getClientOriginalName();
            // Log::info($fullPath);
            if (Storage::disk('private')->exists($endPath)) {
                if (Storage::disk('private')->exists($fullPath) && Storage::disk('private')->size($fullPath) === $file->getSize()) {
                    return [null, 200];
                }

                $file->storeAs($endPath, $file->getClientOriginalName(), 'private');

                $account->usedSize += $file->getSize();
                $account->save();

                return [null, 200];
            }
        } catch (Exception $e) {
            // Log::info($e);
            return [null, 403];
        }
    }
}
