<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@cyberguard.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create default moderator
        User::create([
            'name' => 'System Moderator',
            'email' => 'moderator@cyberguard.com',
            'password' => Hash::make('mod123'),
            'role' => 'moderator',
        ]);

        // Seed help centers and hotlines
        $this->call(HelpCenterSeeder::class);
    }
}
