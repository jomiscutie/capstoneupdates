<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountsFile = database_path('seeders/admin_accounts.json');

        if (File::exists($accountsFile)) {
            $decoded = json_decode(File::get($accountsFile), true);
            $accounts = is_array($decoded) ? $decoded : [];

            foreach ($accounts as $account) {
                if (
                    ! is_array($account)
                    || empty($account['email'])
                    || empty($account['password'])
                ) {
                    continue;
                }

                Admin::updateOrCreate(
                    ['email' => (string) $account['email']],
                    [
                        'name' => (string) ($account['name'] ?? 'System Administrator'),
                        'password' => Hash::make((string) $account['password']),
                        'role' => (string) ($account['role'] ?? 'super_admin'),
                    ]
                );
            }

            return;
        }

        $email = env('ADMIN_EMAIL', 'admin@norsu.test');

        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'System Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin12345')),
            ]
        );
    }
}
