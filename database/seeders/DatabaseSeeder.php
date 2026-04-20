<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Default seed order (production-safe):
     * 1. SettingsSeeder     — brand + SMS + security settings
     * 2. RolesSeeder        — 4 roles + 20 permissions
     * 3. HrEmployeesSeeder  — 803 موظفين من CSV
     *
     * TestUsersSeeder is NOT included — it's for dev-only and must be invoked
     * explicitly: `php artisan db:seed --class=TestUsersSeeder`
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            RolesSeeder::class,
            HrEmployeesSeeder::class,
        ]);
    }
}
