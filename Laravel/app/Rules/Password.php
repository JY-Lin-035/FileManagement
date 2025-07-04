<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

// php artisan make:rule StrongPassword

class Password implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('密碼必須包含至少一個大寫字母');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('密碼必須包含至少一個小寫字母');
        }

        if (!preg_match('/\d/', $value)) {
            $fail('密碼必須包含至少一個數字');
        }

        preg_match_all('/[^A-Za-z0-9]/', $value, $matches);
        $uniqueSymbols = array_unique($matches[0]);
        if (count($uniqueSymbols) < 3) {
            $fail('密碼必須包含至少三種不同的符號');
        }
    }
}
