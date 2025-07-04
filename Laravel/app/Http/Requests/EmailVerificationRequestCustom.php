<?php

namespace App\Http\Requests;

use App\Models\Accounts;

use Illuminate\Validation\Validator;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequestCustom extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! hash_equals((string) Cache::get((string) 'registerVerifyMail:' . $this->route('id')),  (string) $this->route('hash'))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill()
    {
        $user = Accounts::find($this->route('id'));

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->enable = true;
            $user->save();

            event(new Verified($user));
        }
        Cache::forget('registerVerifyMail:' . $this->route('id'));
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return \Illuminate\Validation\Validator
     */
    public function withValidator(Validator $validator)
    {
        return $validator;
    }
}
