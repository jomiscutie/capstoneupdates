<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coordinator;

class CoordinatorSeeder extends Seeder
{
    public function run(): void
    {
        Coordinator::create([
            'name'     => 'Admin',
            'email'    => 'admin@norsu.edu.ph',
            'password' => bcrypt('admin123'), // simple hashed password
            'role'     => 'admin',
        ]);
    }
}
