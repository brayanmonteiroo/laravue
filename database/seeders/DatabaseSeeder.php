<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'name' => 'Brayan',
            'email' => 'brayanmonteirooo@gmail.com',
            'password' => Hash::make('36925814'),
        ]);

        $user->assignRole(RolePermissionSeeder::ROLE_ADMIN);
    }
}
