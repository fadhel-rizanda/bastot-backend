<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Status;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create([
            'id' => 'SUPER_ADMIN',
            'name' => 'Super Admin',
            'description' => 'Super administrator role with all permissions',
        ]);
        Role::create([
            'id' => 'PLAYER',
            'name' => 'Player',
            'description' => 'Player role with limited permissions',
        ]);
        Role::create([
            'id' => 'CAREER_PROVIDER',
            'name' => 'Career Provider',
            'description' => 'Career provider role with specific permissions',
        ]);
        Role::create([
            'id' => 'COURT_OWNER',
            'name' => 'Court Owner',
            'description' => 'Court owner role with specific permissions',
        ]);
        Role::create([
            'id' => 'PF',
            'name' => 'Power Forward',
            'description' => 'Power Forward position in basketball',
        ]);
        Role::create([
            'id' => 'C',
            'name' => 'Center',
            'description' => 'Center position in basketball',
        ]);
        Role::create([
            'id' => 'SF',
            'name' => 'Small Forward',
            'description' => 'Small Forward position in basketball',
        ]);
        Role::create([
            'id' => 'SG',
            'name' => 'Shooting Guard',
            'description' => 'Shooting Guard position in basketball',
        ]);
        Role::create([
            'id' => 'PG',
            'name' => 'Point Guard',
            'description' => 'Point Guard position in basketball',
        ]);

        Status::create([
            'id' => 'ACTIVE',
            'name' => 'Active',
            'description' => 'User is active',
        ]);

        Status::create([
            'id' => 'INACTIVE',
            'name' => 'Inactive',
            'description' => 'User is inactive',
        ]);

        Status::create([
            'id' => 'DEACTIVATED',
            'name' => 'Deactivated',
            'description' => 'User is deactivated',
        ]);

        Status::create([
            'id' => 'BANNED',
            'name' => 'Banned',
            'description' => 'User is banned',
        ]);

//        Role::create([
//            'id' => '',
//            'name' => '',
//            'description' => '',
//        ]);

        // User::factory()->create([
        //     'name' => 'Super Admin',
        //     "email" => "admin123@gmail.com",
        //     "password" => "admin123",
        //     "role" => "SUPER_ADMIN"
        // ]);
        // User::factory()->create([
        //     'name' => 'Player',
        //     "email" => "player123@gmail.com",
        //     "password" => "player123",
        //     "role" => "PLAYER"
        // ]);
        // User::factory()->create([
        //     'name' => 'Career Provider',
        //     "email" => "provider123@gmail.com",
        //     "password" => "provider123",
        //     "role" => "CAREER_PROVIDER"
        // ]);
        // User::factory()->create([
        //     'name' => 'Court Owner',
        //     "email" => "owner123@gmail.com",
        //     "password" => "owner123",
        //     "role" => "COURT_OWNER"
        // ]);
    }
}
