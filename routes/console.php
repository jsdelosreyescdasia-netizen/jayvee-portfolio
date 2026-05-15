<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:make-admin {email}', function (string $email) {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No employee account found for that email.');

        return 1;
    }

    $user->forceFill(['is_admin' => true])->save();

    $this->info($user->name.' is now an admin.');

    return 0;
})->purpose('Promote an employee account to admin');
