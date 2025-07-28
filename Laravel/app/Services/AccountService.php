<?php

namespace App\Services;

use App\Models\Accounts;
use App\Jobs\SendRawMail;
use App\Helpers\LogHelper;
use App\Services\CheckAccountService;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use Exception;

class AccountService
{
    public function register(string $username, string $email, string $password, bool $debug = false)
    {
        try {
            $passwordHash = Hash::make($password);

            $account = Accounts::create([
                'name'     => $username,
                'email'    => $email,
                'password' => $passwordHash,
            ]);

            $folderName = 'users/' . $account->id . '/Home/';
            Storage::disk('private')->makeDirectory($folderName);

            $account->sendEmailVerificationNotification();
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-register: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }

        return null;
    }

    public function login(Accounts $account, string $password, CheckAccountService $check, bool $debug = false)
    {
        try {
            $err = $check->valLogIn($account, $password);

            if ($err) {
                return $err;
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

            return ['token' => $token, 'stateCode' => 200];
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-login: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function signOut(Accounts $account)
    {
        $account->tokens()->where('name', 'session')->delete();
        return null;
    }

    public function modifyMail(Accounts $account, string $mail, string $checkMail, string $code, bool $debug = false)
    {
        try {
            if ($checkMail !== $account->email) {
                return ['error' => '目前電子信箱錯誤', 'stateCode' => 403];
            }

            if ($code !== Cache::get('mail' . 'Code' . $mail)) {
                return ['error' => '驗證碼錯誤', 'stateCode' => 403];
            }

            Cache::forget('mail' . 'Code' . $mail);

            $account->email = $mail;
            $account->email_verified_at = now();
            $account->save();

            return null;
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-modifyMail: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function modifyPassword(Accounts $account, string $nowPW, array $valNewPW, bool $debug = false)
    {
        try {
            if (!Hash::check($nowPW, $account->password)) {
                return ['error' => '目前密碼錯誤', 'stateCode' => 403];
            }

            $passwordHash = Hash::make($valNewPW['newPW']);
            $account->password = $passwordHash;
            $account->save();

            return null;
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-modifyPassword: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function getCode(Accounts $account, string $mail, string $mode, bool $debug = false)
    {
        try {
            if (!$account && $mode == 'pw') {
                return ['error' => '該信箱地址尚未註冊', 'stateCode' => 404];
            }

            if ($account && $mode == 'mail') {
                return ['error' => '該信箱地址已註冊', 'stateCode' => 409];
            }

            $modeList = ['pw' => '密碼重置通知', 'mail' => '信箱變更驗證通知'];
            if (!array_key_exists($mode, $modeList)) {
                return ['error' => 'Error', 'stateCode' => 404];
            }

            $code = Str::random(8);
            Cache::put($mode . 'Code' . $mail, $code, now()->addMinutes((int) env('RESET_PW_CODE_EXPIRE_TIME', 5)));
            SendRawMail::dispatch(
                $mail,
                $modeList[$mode],
                '驗證碼: ' . $code
            );

            return null;
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-getCode: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }

    public function resetPW(Accounts $account, string $mail, string $code, array $valPW, bool $debug = false)
    {
        try {
            if (!$account) {
                return ['error' => '該信箱地址尚未註冊', 'stateCode' => 404];
            }

            if ($code !== Cache::get('pw' . 'Code' . $mail)) {
                return ['error' => '驗證碼錯誤', 'stateCode' => 403];
            }

            Cache::forget('pw' . 'Code' . $mail);

            $account->tokens()->where('name', 'session')->delete();
            $passwordHash = Hash::make($valPW['password']);
            $account->password = $passwordHash;
            $account->save();

            return null;
        } catch (Exception $err) {
            if ($debug) {
                LogHelper::errLog('AccountService-resetPW: ', $err);
                return ['error' => $err->getMessage(), 'stateCode' => 500];
            } else {
                return ['error' => 'Error', 'stateCode' => 500];
            }
        }
    }
}
