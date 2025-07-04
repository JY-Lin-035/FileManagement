<?php

namespace App\Http\Controllers\api;

use App\Rules\Password;
use App\Models\Accounts;
use App\Jobs\SendRawMail;
use App\Helpers\MailAddr;
use App\Services\FolderService;
use App\Http\Requests\RegisterCheck;
use App\Http\Controllers\Controller;
use App\Services\CheckAccountService;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Exception;

class AccountsController extends Controller
{
    public function register(RegisterCheck $request)
    {
        $username = $request->input('username');

        $email = MailAddr::format($request->input('email'));

        $password = $request->input('password');
        $passwordHash = Hash::make($password);

        $account = Accounts::create([
            'name'     => $username,
            'email'    => $email,
            'password' => $passwordHash,
        ]);

        $folderName = 'users/' . $account->id . '/Home/';
        Storage::disk('private')->makeDirectory($folderName);

        try {
            $account->sendEmailVerificationNotification();

            return response()->json(['message' => 'success'], 200);
        } catch (Exception $e) {
            // return response()->json(['error' => $e->getMessage()], 500);
            return response()->json('', 500);
        }
    }

    public function login(Request $request, CheckAccountService $check)
    {
        try {
            $name = $request->input('username');

            $account = Accounts::where('name', $name)->first();
            $err = $check->valLogIn($account, $request->input('password'));

            if ($err) {
                return response()->json(['error' => $err['error']], $err['stateCode']);
            }

            $identity = ['user', 'admin'];
            $account->tokens()->where('name', 'session')->delete();
            $token = $account->createToken(
                'session',
                [$identity[$account->identity]],
                now()->addMinutes((int) env('TOKEN_EXPIRE_TIME', 30))
            )->plainTextToken;

            $used = FolderService::totalUsedSize($account->id);
            if ($account->usedSize !== $used) {
                $account->usedSize = $used;
            }
            $account->lastSignInDate = now();
            $account->save();

            return response()->json([
                'message' => ['登入成功'],
                'email' => MailAddr::lock($account->email),
            ], 200)->cookie('session', $token, (int) env('TOKEN_EXPIRE_TIME', 30), null, null, false, true, false, 'Lax');
        } catch (Exception $e) {
            // Log::error($e);
            // return response()->json(['error' => $e->getMessage()], 500);
            return response()->json('', 500);
        }
    }

    public function signOut(Request $request)
    {
        $account =  Accounts::find($request->attributes->get('userId'));
        $account->tokens()->where('name', 'session')->delete();

        return response()->json(null, 200);
    }

    public function modifyMail(Request $request)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $mail = MailAddr::format($request->input('email'));
        $checkMail = MailAddr::format($request->input('checkEmail'));
        $code = $request->input('code');

        if ($checkMail !== $account->email) {
            return response()->json(['error' => '目前電子信箱錯誤'], 403);
        }

        if ($code !== Cache::get('mail' . 'Code' . $mail)) {
            return response()->json(['error' => '驗證碼錯誤'], 403);
        }

        Cache::forget('mail' . 'Code' . $mail);

        $account->email = $mail;
        $account->email_verified_at = now();
        $account->save();

        return response()->json([
            'email' => MailAddr::lock($mail)
        ], 200);
    }

    public function modifyPassword(Request $request)
    {
        $account = Accounts::find($request->attributes->get('userId'));
        $nowPW = $request->input('nowPW');

        if (!Hash::check($nowPW, $account->password)) {
            return response()->json(['error' => '目前密碼錯誤'], 403);
        }

        $valNewPW = $request->validate([
            'newPW' => ['required', 'string', 'min:12', 'max:100', new Password],
        ]);

        $passwordHash = Hash::make($valNewPW['newPW']);
        $account->password = $passwordHash;
        $account->save();
    }

    public function getCode(Request $request)
    {
        try {
            $mode = $request->input('mode');
            $mail = MailAddr::format($request->input('email'));
            $account = Accounts::where('email', $mail)->first();

            if (!$account && $mode == 'pw') {
                return response()->json(['error' => '該信箱地址尚未註冊'], 404);
            }

            if ($account && $mode == 'mail') {
                return response()->json(['error' => '該信箱地址已註冊'], 409);
            }

            $modeList = ['pw' => '密碼重置通知', 'mail' => '信箱變更驗證通知'];
            if (!array_key_exists($mode, $modeList)) {
                return response()->json(['error' => 'error'], 404);
            }

            $code = Str::random(8);
            Cache::put($mode . 'Code' . $mail, $code, now()->addMinutes((int) env('RESET_PW_CODE_EXPIRE_TIME', 5)));
            SendRawMail::dispatch(
                $mail,
                $modeList[$mode],
                '驗證碼: ' . $code
            );

            return response()->json(['message' => '請至信箱查看通知'], 200);
        } catch (Exception $e) {
            // Log::error($e);
            // return response()->json(['error' => $e->getMessage()], 500);
            return response()->json('', 500);
        }
    }

    public function resetPW(Request $request)
    {
        try {
            $mail = $request->input('email');
            $code = $request->input('code');
            $account = Accounts::where('email', $mail)->first();

            if (!$account) {
                return response()->json(['error' => '該信箱地址尚未註冊'], 404);
            }

            if ($code !== Cache::get('pw' . 'Code' . $mail)) {
                return response()->json(['error' => '驗證碼錯誤'], 403);
            }

            $valPW = $request->validate([
                'password' => ['required', 'string', 'min:12', 'max:100', new Password],
            ]);

            Cache::forget('pw' . 'Code' . $mail);

            $account->tokens()->where('name', 'session')->delete();
            $passwordHash = Hash::make($valPW['password']);
            $account->password = $passwordHash;
            $account->save();

            return response()->json(['message' => 'success'], 200);
        } catch (Exception $e) {
            // Log::error($e);
            // return response()->json(['error' => $e->getMessage()], 500);
            return response()->json('', 500);
        }
    }
}
