<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminAccountSeeder extends Seeder
{
    /**
     * Seed the default system administrator account.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@daily-report.local'],
            [
                'name' => 'System Admin',
                'password' => 'Admin12345',
                'is_admin' => true,
            ],
        );
    }
}
