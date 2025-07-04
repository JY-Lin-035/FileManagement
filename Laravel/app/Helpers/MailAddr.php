<?php

namespace App\Helpers;

class MailAddr
{
    public static function format(string $email): string
    {
        $email = explode('@', strtolower($email));
        $email[0] = $email[1] === 'gmail.com' ? str_replace('.', '', $email[0]) : $email[0];
        $email = implode('@', $email);

        return $email;
    }

    public static function lock(string $email, ): string
    {
        [$name, $domain] = explode('@', $email, 2);

        $l = strlen($name);
        if ($l <= 2) {
            $name = str_repeat('*', 5);
        }
        else {
            $name = $name[0] . '*****' . $name[$l - 1];
        }

        $email = $name . '@' . $domain;

        return $email;
    }
}
