<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'student@campusconnect.test'],
            [
                'name' => 'Student User',
                'password' => 'password',
                'role' => User::ROLE_STUDENT,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@campusconnect.test'],
            [
                'name' => 'Staff User',
                'password' => 'password',
                'role' => User::ROLE_STAFF,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@campusconnect.test'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ],
        );
    }
}
