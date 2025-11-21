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

        $result = ReturnHelper::controllerReturn($result, $result['date']);
        
        return response()->json($result['data'], $result['stateCode']);
    }

    public function renameFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $originName = $request->input('originName');
        $newName = $request->input('folderName');

        $result = $folderService->rename($userID, $dir, $originName, $newName);

        $result = ReturnHelper::controllerReturn($result, ['message' => $result['msg']]);

        return response()->json($result['data'], $result['stateCode']);
    }

    public function deleteFolder(Request $request, FolderService $folderService)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $folderName = $request->query('folderName');

        $result = $folderService->delete($userID, $dir, $folderName);

        $result = ReturnHelper::controllerReturn($result, ['size' => $result['size']]);

        return response()->json($result['data'], $result['stateCode']);
    }
}
