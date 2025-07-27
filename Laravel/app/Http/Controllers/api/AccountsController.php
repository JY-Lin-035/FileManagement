<?php

namespace App\Http\Controllers\api;

use App\Rules\Password;
use App\Models\Accounts;
use App\Helpers\MailAddr;
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

        $err = $accountService->register($username, $email, $password);

        if ($err) {
            return response()->json(['error' => $err['error']], $err['stateCode']);
        } else {
            return response()->json(['message' => 'success'], 200);
        }
    }

    public function login(Request $request, CheckAccountService $check, AccountService $accountService)
    {
        $name = $request->input('username');
        $account = Accounts::where('name', $name)->first();

        $token = $accountService->login($account, $request->input('password'), $check);

        if (is_array($token)) {
            return response()->json(['error' => $token['error']], $token['stateCode']);
        } else {
            return response()->json([
                'message' => ['登入成功'],
                'email' => MailAddr::lock($account->email),
            ], 200)->cookie('session', $token, (int) env('TOKEN_EXPIRE_TIME', 30), null, null, false, true, false, 'Lax');
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

        $err = $accountService->modifyMail($account, $mail, $checkMail, $code);

        if ($err) {
            return response()->json(['error' => $err['error']], $err['stateCode']);
        }

        return response()->json([
            'email' => MailAddr::lock($mail)
        ], 200);
    }

    public function modifyPassword(Request $request, AccountService $accountService)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $nowPW = $request->input('nowPW');
        $valNewPW = $request->validate([
            'newPW' => ['required', 'string', 'min:12', 'max:100', new Password],
        ]);

        $err = $accountService->modifyPassword($account, $nowPW, $valNewPW);

        if ($err) {
            return response()->json(['error' => $err['error']], $err['stateCode']);
        } else {
            return response()->json(['message' => 'success'], 200);
        }
    }

    public function getCode(Request $request, AccountService $accountService)
    {
        $mode = $request->input('mode');
        $mail = MailAddr::format($request->input('email'));
        $account = Accounts::where('email', $mail)->first();

        $err = $accountService->getCode($account, $mail, $mode);

        if ($err) {
            return response()->json(['error' => $err['error']], $err['stateCode']);
        } else {
            return response()->json(['message' => '請至信箱查看通知'], 200);
        }
    }

    public function resetPW(Request $request, AccountService $accountService)
    {
        $mail = $request->input('email');
        $code = $request->input('code');
        $account = Accounts::where('email', $mail)->first();
        $valPW = $request->validate([
            'password' => ['required', 'string', 'min:12', 'max:100', new Password],
        ]);

        $err = $accountService->resetPW($account, $mail, $code, $valPW);

        if ($err) {
            return response()->json(['error' => $err['error']], $err['stateCode']);
        } else {
            return response()->json(['message' => 'success'], 200);
        }
    }
}
