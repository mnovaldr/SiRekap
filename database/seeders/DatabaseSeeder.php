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

        User::factory()->create([
            'name' => 'Admin MCC',
            'email' => 'admin@mcc.com',
            'password' => bcrypt('Admin@12345'),
        ]);

        // Seed master data
        $this->call(MasterDataSeeder::class);
    }
}
