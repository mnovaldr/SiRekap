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
        // User::factory(10)->create();

        $adminEmail = env('ADMIN_DEFAULT_EMAIL', 'admin@mcc.com');
        $adminPassword = env('ADMIN_DEFAULT_PASSWORD', 'password');

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin MCC',
                'password' => bcrypt($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        // Seed master data
        $this->call(MasterDataSeeder::class);
    }
}
