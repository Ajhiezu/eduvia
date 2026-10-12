<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with safe development roles.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Password123!');

        // 1. Super Admin Platform
        User::firstOrCreate(
            ['email' => 'superadmin@eduvia.test'],
            [
                'name' => 'Super Administrator Eduvia',
                'role' => UserRole::SUPER_ADMIN->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 2. School Admin (Admin Sekolah)
        User::firstOrCreate(
            ['email' => 'admin.sekolah@eduvia.test'],
            [
                'name' => 'Admin Sekolah Nusantara',
                'role' => UserRole::SCHOOL_ADMIN->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 3. Teacher (Guru)
        User::firstOrCreate(
            ['email' => 'guru@eduvia.test'],
            [
                'name' => 'Budi Santoso, S.Pd.',
                'role' => UserRole::TEACHER->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 4. Student (Siswa)
        User::firstOrCreate(
            ['email' => 'siswa@eduvia.test'],
            [
                'name' => 'Ahmad Fauzi',
                'role' => UserRole::STUDENT->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );

        // 5. Parent (Orang Tua / Wali)
        User::firstOrCreate(
            ['email' => 'orangtua@eduvia.test'],
            [
                'name' => 'Hendra Setiawan (Wali)',
                'role' => UserRole::PARENT->value,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => $defaultPassword,
            ]
        );
    }
}
