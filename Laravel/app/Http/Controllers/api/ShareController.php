<?php

namespace App\Http\Controllers\api;

use App\Models\Accounts;
use App\Services\ShareService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Exception;

class ShareController extends Controller
{
    public function getList(Request $request, ShareService $shareService)
    {
        try {
            $account = Accounts::find($request->attributes->get('userId'));
            $list = $shareService->getList($account);

            return response()->json(['share' => $list], 200);
        } catch (Exception $e) {
            // Log::info($e);
            return response()->json(null, 404);
        }
    }

    public function getLink(Request $request, ShareService $shareService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $dir = $request->input('dir');
        $fileName = $request->input('filename');

        $url = $shareService->shareLink($account, $dir, $fileName, null, 'generate');

        return response()->json($url[0], $url[1]);
    }

    public function deleteLink(Request $request, ShareService $shareService)
    {

        $account = Accounts::find($request->attributes->get('userId'));
        $dir = $request->query('dir');
        $fileName = $request->query('filename');
        $link = $request->query('link');

        if ($dir && $fileName) {
            $url = $shareService->shareLink($account, $dir, $fileName, null, 'signalDelete');
        } else if ($link) {
            $url = $shareService->shareLink($account, null, null, $link, 'listDelete');
        } else {
            return response()->json(null, 404);
        }
        return response()->json($url[0], $url[1]);
    }

    public function downloadFile(Request $request)
    {
        $link = $request->query('link');

        try {
            $result = ShareService::download($link);

            if ($result === 404) {
                return 404;
            } else {
                return $result;
            }
        } catch (Exception $e) {
            return 404;
        }
    }
}
