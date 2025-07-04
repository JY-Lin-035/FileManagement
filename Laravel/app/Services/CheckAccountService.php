<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class CheckAccountService
{
    public function valLogin($account, $password)
    {
        if (!$account) {
            return ['error' => '使用者名稱不存在', 'stateCode' => 404];
        }

        if (!Hash::check($password, $account->password)) {
            return ['error' => '密碼錯誤!', 'stateCode' => 401];
        }

        if (!$account->email_verified_at) {
            $account->sendEmailVerificationNotification();

            return ['error' => '信箱地址尚未驗證!', 'stateCode' => 403];
        }

        if (!$account->enable) {
            return ['error' => '目前該帳號位於小黑屋，如有疑慮請聯絡管理員', 'stateCode' => 403];
        }

        if ($account->deleteDate) {
            return ['error' => '帳號已刪除，如有疑慮請聯絡管理員', 'stateCode' => 403];
        }

        return null;
    }
}
