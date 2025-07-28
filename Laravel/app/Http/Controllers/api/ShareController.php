<?php

namespace App\Http\Controllers\api;

use App\Models\Accounts;
use App\Helpers\ReturnHelper;
use App\Services\ShareService;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function getList(Request $request, ShareService $shareService)
    {
        $account = Accounts::find($request->attributes->get('userId'));

        $result = $shareService->getList($account);

        ReturnHelper::controllerReturn(
            $result,
            response()->json(['share' => $result['list']], $result['stateCode'])
        );
    }

    public function getLink(Request $request, ShareService $shareService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $dir = $request->input('dir');
        $fileName = $request->input('filename');

        $result = $shareService->shareLink($account, $dir, $fileName, null, 'generate');

        ReturnHelper::controllerReturn(
            $result,
            response()->json($result['url'], $result['stateCode'])
        );
    }

    public function deleteLink(Request $request, ShareService $shareService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $dir = $request->query('dir');
        $fileName = $request->query('filename');
        $link = $request->query('link');

        if ($dir && $fileName) {
            $result = $shareService->shareLink($account, $dir, $fileName, null, 'singleDelete');
        } else if ($link) {
            $result = $shareService->shareLink($account, null, null, $link, 'listDelete');
        } else {
            return response()->json(['error' => 'Error'], 404);
        }

        ReturnHelper::controllerReturn(
            $result,
            response()->json($result['msg'], $result['stateCode'])
        );
    }

    public function downloadFile(Request $request, ShareService $shareService)
    {
        $link = $request->query('link');
        $result = $shareService->download($link);

        ReturnHelper::controllerReturn(
            $result,
            response()->download($result['realPath'], $result['fileName'])
        );
    }
}
