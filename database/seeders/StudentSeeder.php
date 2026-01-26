<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        Student::create([
            'student_no' => 'NORSU-2025-0001',
            'name'       => 'Juan Dela Cruz',
            'course'     => 'BSIT',
            'password'   => bcrypt('password123'), // simple hash
        ]);
    }
}
