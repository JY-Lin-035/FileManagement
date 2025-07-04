<?php

namespace App\Services;

use App\Models\ShareLink;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Exception;

class ShareService
{
    public function getList($account)
    {
        $list = [];
        $shares = $account->shareLinks;
        // Log::info($shares);
        if ($shares) {
            foreach ($shares as $item) {
                $list[] = ['name' => $item->fileName, 'path' => $item->path, 'link' => $item->link, 'date' => date('Y-m-d H:i:s', $item->updated_at->timestamp + (8 * 60 * 60))];
            }
        }

        return $list;
    }

    public function shareLink($account, $dir, $fileName, $link, $mode): array
    {
        if ($mode === 'listDelete') {
            try {
                $account->shareLinks()->where([
                    ['owner_id', '=', $account->id],
                    ['link', '=', $link]
                ])->delete();

                return [null, 200];
            } catch (Exception $e) {
                // Log::info($e);
                return [null, 403];
            }
        }

        $dir = str_replace('-', '/', base64_decode(strtr($dir, '-_', '+/')));
        $path = storage_path('app/private/users/' . $account->id . '/' . $dir);
        $fullPath = $path . '/' . $fileName;
        $realPath = realpath($fullPath);

        if (
            !$realPath || !str_contains($realPath, 'app\\private\\users\\' . $account->id . '\\' . 'Home') ||
            str_contains($dir, '../') || str_contains($dir, '..\\') ||
            str_contains($dir, './') || str_contains($dir, '.\\') ||
            str_starts_with($dir, '/') || str_starts_with($dir, '\\') ||
            str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
            str_contains($fileName, '../') || str_contains($fileName, '..\\') ||
            str_contains($fileName, './') || str_contains($fileName, '.\\') ||
            str_starts_with($fileName, '/') || str_starts_with($fileName, '\\') ||
            str_starts_with($fileName, '.')
        ) {
            return [null, 404];
        }

        $endPath = 'users/' . $account->id . '/' . $dir . '/' . $fileName;

        if ($mode === 'generate') {
            $url = $account->shareLinks()->where([
                ['owner_id', '=', $account->id],
                ['path', '=', $dir],
                ['filename', '=', $fileName],
            ])->first();

            if ($url) {
                return [$url['link'], 200];
            }

            if (Storage::disk('private')->exists($endPath)) {
                try {
                    $url = hash('sha512', $dir . now() . $fileName . Str::random(8));

                    $account->shareLinks()->create([
                        'link' => $url,
                        'owner_id' => $account->id,
                        'path' => $dir,
                        'fileName' => $fileName,
                        'created_at' => now(),
                    ]);

                    return [$url, 200];
                } catch (Exception $e) {
                    // Log::info($e);
                    return [null, 403];
                }
            }
        } else if ($mode === 'signalDelete') {
            try {
                $account->shareLinks()->where([
                    ['owner_id', '=', $account->id],
                    ['path', '=', $dir],
                    ['filename', '=', $fileName],
                ])->delete();

                return [null, 200];
            } catch (Exception $e) {
                // Log::info($e);
                return [null, 403];
            }
        }

        return [null, 404];
    }

    public static function download($link)
    {
        try {
            $fileInfo = ShareLink::where('link', $link)->first();

            if (!$fileInfo) {
                return 404;
            }

            $path = 'users/' . $fileInfo->owner_id . '/' . $fileInfo->path . '/' . $fileInfo->fileName;
            $fullPath = storage_path('app/private/' . $path);
            $realPath = realpath($fullPath);
            if (Storage::disk('private')->exists($path)) {
                return response()->download($realPath, $fileInfo->fileName);
            }

            return 404;
        } catch (Exception $e) {
            // Log::info($e);
            return 404;
        }
    }
}
