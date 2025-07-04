<?php

namespace App\Http\Controllers\api;

use App\Services\FolderService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class FolderController extends Controller
{
    public function createFolder(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $newName = $request->input('folderName');

        // Log::info($dir);
        // Log::info($originName);
        // Log::info($newName);
        $result = FolderService::create($userID, $dir, $newName);
        return response()->json($result[0], $result[1]);
    }

    public function renameFolder(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->input('dir');
        $originName = $request->input('originName');
        $newName = $request->input('folderName');

        // Log::info($dir);
        // Log::info($originName);
        // Log::info($newName);

        $result = FolderService::rename($userID, $dir, $originName, $newName);
        return response()->json($result[0], $result[1]);
    }

    public function deleteFolder(Request $request)
    {
        $userID = $request->attributes->get('userId');
        $dir = $request->query('dir');
        $folderName = $request->query('folderName');

        // Log::info($dir);
        // Log::info($folderName);

        $result = FolderService::delete($userID, $dir, $folderName);
        return response()->json($result[0], $result[1]);
    }
}
