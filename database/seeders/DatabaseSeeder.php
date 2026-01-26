<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Database\Seeders\StudentSeeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StudentSeeder::class,
            CoordinatorSeeder::class,
        ]);
    }
}
