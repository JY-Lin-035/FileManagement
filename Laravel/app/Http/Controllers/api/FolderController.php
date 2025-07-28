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

        ReturnHelper::controllerReturn(
            $result,
            response()->json($result['date'], $result['stateCode'])
        );
    }

    public function renameFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $originName = $request->input('originName');
        $newName = $request->input('folderName');

        $result = $folderService->rename($userID, $dir, $originName, $newName);

        ReturnHelper::controllerReturn(
            $result,
            response()->json(['message' => $result['msg']], $result['stateCode'])
        );
    }

    public function deleteFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $folderName = $request->query('folderName');

        $result = $folderService->delete($userID, $dir, $folderName);

        ReturnHelper::controllerReturn(
            $result,
            response()->json(['size' => $result['size']], $result['stateCode'])
        );
    }
}
