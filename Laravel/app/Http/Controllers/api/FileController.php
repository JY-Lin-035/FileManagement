<?php

namespace App\Http\Controllers\api;

use App\Models\Accounts;
use App\Helpers\ReturnHelper;
use App\Services\FileService;
use App\Services\FolderService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Exception;

class FileController extends Controller
{
    public function getStorage(Request $request)
    {
        try {
            $account = Accounts::find($request->attributes->get('userId'));

            return response()->json([
                'usedStorage' => $account->usedSize, //FormatFileSize::formatFileSize($account->usedSize),
                // 'availableStorage' => , //FormatFileSize::formatFileSize($account->totalFileSize - $account->usedSize),
                'signalStorage' => $account->signalFileSize, //FormatFileSize::formatFileSize($account->signalFileSize),
                'totalStorage' => $account->totalFileSize, //FormatFileSize::formatFileSize($account->totalFileSize),
                // 'percentage' => round(($account->usedSize / $account->totalFileSize) * 100, 2)
            ], 200);
        } catch (Exception $err) {
            return response()->json(['error' => 'error'], 500);
        }
    }

    public function getFileList(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $folder = $request->query('path');

        $result = $folderService->getContent($userID, $folder);

        ReturnHelper::controllerReturn(
            $result,
            response()->json(['file' => $result['fileList']], $result['stateCode'])
        );
    }

    public function download(Request $request, FileService $fileService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $fileName = $request->query('filename');

        $result = $fileService->download($userID, $dir, $fileName);

        ReturnHelper::controllerReturn(
            $result,
            response()->download($result['realPath'], $result['fileName'])
        );
    }

    public function delete(Request $request, FileService $fileService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $fileName = $request->query('filename');

        $result = $fileService->delete($userID, $dir, $fileName);

        ReturnHelper::controllerReturn(
            $result,
            response()->json(['size' => $result['size']], $result['stateCode'])
        );
    }

    public function uploadFile(Request $request, FileService $fileService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');

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
            $result = $fileService->upload($userID, $dir, $file);

            ReturnHelper::controllerReturn(
                $result,
                response()->json(['message' => $result['msg']], $result['stateCode'])
            );
        } else {
            return response()->json(null, 403);
        }
    }
}
