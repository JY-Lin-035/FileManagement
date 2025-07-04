<?php

namespace App\Http\Controllers\api;

use App\Models\Accounts;
use App\Services\FileService;
use App\Helpers\FormatFileSize;
use App\Services\FolderService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Exception;

class FileController extends Controller
{
    public function getStorage(Request $request)
    {
        $account = Accounts::find($request->attributes->get('userId'));

        return response()->json([
            'usedStorage' => $account->usedSize, //FormatFileSize::formatFileSize($account->usedSize),
            // 'availableStorage' => , //FormatFileSize::formatFileSize($account->totalFileSize - $account->usedSize),
            'signalStorage' => $account->signalFileSize, //FormatFileSize::formatFileSize($account->signalFileSize),
            'totalStorage' => $account->totalFileSize, //FormatFileSize::formatFileSize($account->totalFileSize),
            // 'percentage' => round(($account->usedSize / $account->totalFileSize) * 100, 2)
        ], 200);
    }

    public function getFileList(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $folder = $request->query('path');

        $fileList = FolderService::getContent($userID, $folder);
        return response()->json(['file' => $fileList], 200);
    }

    public function download(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $fileName = $request->query('filename');

        try {
            $result = FileService::download($userID, $dir, $fileName);

            if ($result === 404) {
                return response()->json(null, 404);
            } else {
                return $result;
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    public function delete(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $fileName = $request->query('filename');

        try {
            $result = FileService::delete($userID, $dir, $fileName);
            if ($result === 404) {
                return response()->json(null, 404);
            } else {
                return response()->json(['size' => $result], 200);
            }
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    public function uploadFile(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        // Log::info($userID . $dir);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Log::info('File upload details', [
            //     'original_name' => $file->getClientOriginalName(),
            //     'original_extension' => $file->getClientOriginalExtension(),
            //     'mime_type' => $file->getClientMimeType(),
            //     'size' => $file->getSize(),
            //     'temp_path' => $file->getRealPath(),
            //     'is_valid' => $file->isValid(),
            //     'error' => $file->getError()
            // ]);
            // return 200;
            $result = FileService::upload($userID, $dir, $file);

            return response()->json($result[0], $result[1]);
        } else {
            return response()->json(null, 403);
        }
    }
}
