<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@docuverify.test'],
            [
                'name' => 'DocuVerify Administrator',
                'password' => Hash::make('admin123456'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
