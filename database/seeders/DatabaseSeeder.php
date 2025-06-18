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
            'type' => 'AUTH',
            'description' => 'Super administrator role with all permissions',
        ]);
        Role::create([
            'id' => 'PLAYER',
            'name' => 'Player',
            'type' => 'AUTH',
            'description' => 'Player role with limited permissions',
        ]);
        Role::create([
            'id' => 'CAREER_PROVIDER',
            'name' => 'Career Provider',
            'type' => 'AUTH',
            'description' => 'Career provider role with specific permissions',
        ]);
        Role::create([
            'id' => 'COURT_OWNER',
            'name' => 'Court Owner',
            'type' => 'AUTH',
            'description' => 'Court owner role with specific permissions',
        ]);
        Role::create([
            'id' => 'PF',
            'name' => 'Power Forward',
            'type' => 'BASKETBALL',
            'description' => 'Power Forward position in basketball',
        ]);
        Role::create([
            'id' => 'C',
            'name' => 'Center',
            'type' => 'BASKETBALL',
            'description' => 'Center position in basketball',
        ]);
        Role::create([
            'id' => 'SF',
            'name' => 'Small Forward',
            'type' => 'BASKETBALL',
            'description' => 'Small Forward position in basketball',
        ]);
        Role::create([
            'id' => 'SG',
            'name' => 'Shooting Guard',
            'type' => 'BASKETBALL',
            'description' => 'Shooting Guard position in basketball',
        ]);
        Role::create([
            'id' => 'PG',
            'name' => 'Point Guard',
            'type' => 'BASKETBALL',
            'description' => 'Point Guard position in basketball',
        ]);

        Status::create([
            'id' => 'ACTIVE',
            'name' => 'Active',
            'description' => 'User is active',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'INACTIVE',
            'name' => 'Inactive',
            'description' => 'User is inactive',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'DEACTIVATED',
            'name' => 'Deactivated',
            'description' => 'User is deactivated',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'BANNED',
            'name' => 'Banned',
            'description' => 'User is banned',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'PENDING',
            'name' => 'Pending',
            'description' => 'User is pending approval',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'INVITED',
            'name' => 'Invited',
            'description' => 'User is invited',
            'type' => 'GENERAL',
        ]);

        Status::create([
            'id' => 'SCHEDULED',
            'name' => 'Scheduled',
            'description' => 'Game is scheduled',
            'type' => 'GAME',
        ]);

        Status::create([
            'id' => 'ONGOING',
            'name' => 'Ongoing',
            'description' => 'Game is ongoing',
            'type' => 'GAME',
        ]);

        Status::create([
            'id' => 'COMPLETED',
            'name' => 'Completed',
            'description' => 'Game is completed',
            'type' => 'GAME',
        ]);

        Status::create([
            'id' => 'CANCELLED',
            'name' => 'Cancelled',
            'description' => 'Game is cancelled',
            'type' => 'GAME',
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
