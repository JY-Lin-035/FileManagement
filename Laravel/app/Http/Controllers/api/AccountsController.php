<?php

namespace App\Http\Controllers\api;

use App\Rules\Password;
use App\Models\Accounts;
use App\Helpers\MailAddr;
use App\Helpers\ReturnHelper;
use App\Services\AccountService;
use App\Http\Requests\RegisterCheck;
use App\Http\Controllers\Controller;
use App\Services\CheckAccountService;

use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function register(RegisterCheck $request, AccountService $accountService)
    {
        $username = $request->input('username');
        $email = MailAddr::format($request->input('email'));
        $password = $request->input('password');

        $result = $accountService->register($username, $email, $password);

        $result = ReturnHelper::controllerReturn($result, ['message' => 'success']);

        return response()->json($result['data'], $result['stateCode']);
    }

    public function login(Request $request, CheckAccountService $check, AccountService $accountService)
    {
        $name = $request->input('username');
        $account = Accounts::where('name', $name)->first();

        $result = $accountService->login($account, $request->input('password'), $check);

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['stateCode']);
        } else {
            return response()->json([
                'message' => ['登入成功'],
                'email' => MailAddr::lock($account->email),
            ], $result['stateCode'])->cookie('session', $result['token'], (int) env('TOKEN_EXPIRE_TIME', 30), null, null, false, true, false, 'Lax');
        }
    }

    public function signOut(Request $request, AccountService $accountService)
    {
        $account =  Accounts::find($request->attributes->get('userId'));

        $accountService->signOut($account);

        return response()->json(null, 200);
    }

    public function modifyMail(Request $request, AccountService $accountService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $mail = MailAddr::format($request->input('email'));
        $checkMail = MailAddr::format($request->input('checkEmail'));
        $code = $request->input('code');

        $result = $accountService->modifyMail($account, $mail, $checkMail, $code);

        $result = ReturnHelper::controllerReturn($result, ['email' => MailAddr::lock($mail)]);

        return response()->json($result['data'], $result['stateCode']);
    }

    public function modifyPassword(Request $request, AccountService $accountService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $nowPW = $request->input('nowPW');
        $valNewPW = $request->validate([
            'newPW' => ['required', 'string', 'min:12', 'max:100', new Password],
        ]);

        $result = $accountService->modifyPassword($account, $nowPW, $valNewPW);

        $result = ReturnHelper::controllerReturn($result, ['message' => 'success']);

        return response()->json($result['data'], $result['stateCode']);
    }

    public function getCode(Request $request, AccountService $accountService)
    {
        $mode = $request->input('mode');
        $mail = MailAddr::format($request->input('email'));
        $account = Accounts::where('email', $mail)->first();

        $result = $accountService->getCode($account, $mail, $mode);

        $result = ReturnHelper::controllerReturn($result, ['message' => '請至信箱查看通知']);

        return response()->json($result['data'], $result['stateCode']);
    }

    public function resetPW(Request $request, AccountService $accountService)
    {
        $mail = $request->input('email');
        $code = $request->input('code');
        $account = Accounts::where('email', $mail)->first();
        $valPW = $request->validate([
            'password' => ['required', 'string', 'min:12', 'max:100', new Password],
        ]);

        $result = $accountService->resetPW($account, $mail, $code, $valPW);

        ReturnHelper::controllerReturn($result, ['message' => 'success']);

        return response()->json($result['data'], $result['stateCode']);
    }
}
