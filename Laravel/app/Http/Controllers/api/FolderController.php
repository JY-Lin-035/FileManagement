<?php

namespace App\Http\Controllers\api;

use App\Helpers\ReturnHelper;
use App\Services\FolderService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class FolderController extends Controller
{
    public function createFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $newName = $request->input('folderName');

        $result = $folderService->create($userID, $dir, $newName);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['stateCode']);
        } else {
            return response()->json($result['date'], 200);
        }
    }

    public function renameFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $originName = $request->input('originName');
        $newName = $request->input('folderName');

        $result = $folderService->rename($userID, $dir, $originName, $newName);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['stateCode']);
        } else {
            return response()->json(['message' => $result['msg']], 200);
        }
    }

    public function deleteFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $folderName = $request->query('folderName');

        $result = $folderService->delete($userID, $dir, $folderName);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['stateCode']);
        } else {
            return response()->json(['size' => $result['size']], 200);
        }
    }
}
