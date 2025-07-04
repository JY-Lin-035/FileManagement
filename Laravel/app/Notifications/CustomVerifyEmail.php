<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(Lang::get('Meow 請驗證電子信箱地址'))
            // ->line(Lang::get('請點擊下面的按鈕來驗證您的電子信箱地址。'))
            // ->action(Lang::get('驗證電子信箱'), $url)
            // ->line(Lang::get('如果您沒有註冊帳戶，則無需採取進一步操作。'));
            ->markdown('email.verify', ['url' => $url]);
    }

    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        $id = $notifiable->getKey();
        $hash = hash('sha256', $notifiable->getEmailForVerification() . now());


        Cache::put('registerVerifyMail:' . $id, $hash, now()->addMinutes((int) env('MAIL_VAL_EXPIRE_TIME', 30)));

        return URL::temporarySignedRoute(
            'verify.verification',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', (int) env('MAIL_VAL_EXPIRE_TIME', 30))),
            [
                'id' => $id,
                'hash' => $hash,
            ]
        );
    }
}
