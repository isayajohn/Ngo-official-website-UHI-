<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function rules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            self::strength(),
        ];
    }

    public static function strength(): Password
    {
        return Password::min(12)->mixedCase()->numbers()->symbols();
    }
}
