<?php

namespace App\Http\Requests;

use App\Helpers\MailAddr;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCheck extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[A-Za-z0-9]+$/',
            ],
            'email'    => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $value = MailAddr::format($value);

                    $input = $this->all();

                    $nameExists = DB::table('accounts')->where('name', $input['username'])->exists();
                    if ($nameExists) {
                        return $fail('此用戶名已被使用');
                    }

                    $emailExists = DB::table('accounts')->where('email', $value)->exists();
                    if ($emailExists) {
                        return $fail('此電子信箱地址已被使用');
                    }
                }
            ],
            'password' => [
                'required',
                'string',
                'min:12',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/[A-Z]/', $value)) {
                        return $fail('密碼必須包含至少一個大寫字母');
                    }

                    if (!preg_match('/[a-z]/', $value)) {
                        return $fail('密碼必須包含至少一個小寫字母');
                    }

                    if (!preg_match('/\d/', $value)) {
                        return $fail('密碼必須包含至少一個數字');
                    }

                    preg_match_all('/[^A-Za-z0-9]/', $value, $matches);
                    $uniqueSymbols = array_unique($matches[0]);
                    if (count($uniqueSymbols) < 3) {
                        return $fail('密碼必須包含至少三種不同的符號');
                    }
                },
            ],
        ];
    }
}
