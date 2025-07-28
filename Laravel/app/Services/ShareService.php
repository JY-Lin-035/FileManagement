<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ShareLink;
use App\Helpers\LogHelper;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Exception;

class ShareService
{
    public function getList(Accounts $account, bool $debug = false)
    {
        try {
            $list = [];
            $shares = $account->shareLinks;

            if ($shares) {
                foreach ($shares as $item) {
                    $list[] = ['name' => $item->fileName, 'path' => $item->path, 'link' => $item->link, 'date' => date('Y-m-d H:i:s', $item->updated_at->timestamp + (8 * 60 * 60))];
                }
            }

            return ['list' => $list, 'stateCode' => 200];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('ShareService-getList: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function shareLink(Accounts $account, ?string $dir, ?string $fileName, ?string $link, string $mode, bool $debug = false)
    {
        try {
            if ($mode === 'listDelete') {
                try {
                    $account->shareLinks()->where([
                        ['owner_id', '=', $account->id],
                        ['link', '=', $link]
                    ])->delete();

                    return ['msg' => 'success', 'stateCode' => 200];
                } catch (Exception $err) {
                    if ($debug) {
                        LogHelper::errLog('ShareService-shareLink-listDelete: ', $err);
                        return ['error' => $err->getMessage(), 'stateCode' => 500];
                    } else {
                        return ['error' => 'Error', 'stateCode' => 500];
                    }
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
                    return ['url' => $url['link'], 'stateCode' => 200];
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

                        return ['url' => $url['link'], 'stateCode' => 200];
                    } catch (Exception $err) {
                        if ($debug) {
                            LogHelper::errLog('ShareService-shareLink-generate: ', $err);
                            return ['error' => $err->getMessage(), 'stateCode' => 500];
                        } else {
                            return ['error' => 'Error', 'stateCode' => 500];
                        }
                    }
                }
            } else if ($mode === 'singleDelete') {
                try {
                    $account->shareLinks()->where([
                        ['owner_id', '=', $account->id],
                        ['path', '=', $dir],
                        ['filename', '=', $fileName],
                    ])->delete();

                    return ['msg' => 'success', 'stateCode' => 200];
                } catch (Exception $err) {
                    if ($debug) {
                        LogHelper::errLog('ShareService-shareLink-singleDelete: ', $err);
                        return ['error' => $err->getMessage(), 'stateCode' => 500];
                    } else {
                        return ['error' => 'Error', 'stateCode' => 500];
                    }
                }
            }

            return ['error' => 'Error', 'stateCode' => 404];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('ShareService-shareLink: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function download(string $link, bool $debug = false)
    {
        try {
            $fileInfo = ShareLink::where('link', $link)->first();

            if (!$fileInfo) {
                return ['error' => 'Error', 'stateCode' => 404];
            }

            $path = 'users/' . $fileInfo->owner_id . '/' . $fileInfo->path . '/' . $fileInfo->fileName;
            $fullPath = storage_path('app/private/' . $path);
            $realPath = realpath($fullPath);
            if (Storage::disk('private')->exists($path)) {
                return ['realPath' => $realPath, 'fileName' => $fileInfo->fileName];
            }

            return ['error' => 'Error', 'stateCode' => 404];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('ShareService-download: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }
}
