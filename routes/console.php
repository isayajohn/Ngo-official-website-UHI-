<?php

use App\Models\User;
use App\Support\PasswordRules;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

Artisan::command('ughi:admin', function () {
    if (! Role::where('name', 'Super Admin')->where('guard_name', 'web')->exists()) {
        $this->error('Run migrations and database seeders before creating an administrator.');

        return 1;
    }
    $name = $this->ask('Name', 'UGHI Administrator');
    $email = $this->ask('Email');
    $password = $this->secret('Password (12+ characters, mixed case, number and symbol)');
    $confirmation = $this->secret('Confirm password');
    $validator = Validator::make([
        'name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation,
    ], [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => PasswordRules::rules(),
    ]);
    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }
    $user = User::create(['name' => $name, 'email' => $email, 'password' => $password, 'must_change_password' => false]);
    $user->assignRole('Super Admin');
    $this->info('Administrator created. Sign in at /admin.');

    return 0;
})->purpose('Securely provision a new UGHI administrator without a default password');
