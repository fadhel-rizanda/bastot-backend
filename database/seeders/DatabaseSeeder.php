<?php

namespace Database\Seeders;

use App\Enums\Type;
use App\Models\Role;
use App\Models\Status;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            'type' => Type::AUTH,
            'description' => 'Super administrator role with all permissions',
        ]);
        Role::create([
            'id' => 'PLAYER',
            'name' => 'Player',
            'type' => Type::AUTH,
            'description' => 'Player role with limited permissions',
        ]);
        Role::create([
            'id' => 'CAREER_PROVIDER',
            'name' => 'Career Provider',
            'type' => Type::AUTH,
            'description' => 'Career provider role with specific permissions',
        ]);
        Role::create([
            'id' => 'COURT_OWNER',
            'name' => 'Court Owner',
            'type' => Type::AUTH,
            'description' => 'Court owner role with specific permissions',
        ]);
        Role::create([
            'id' => 'PF',
            'name' => 'Power Forward',
            'type' => Type::BASKETBALL,
            'description' => 'Power Forward position in basketball',
        ]);
        Role::create([
            'id' => 'C',
            'name' => 'Center',
            'type' => Type::BASKETBALL,
            'description' => 'Center position in basketball',
        ]);
        Role::create([
            'id' => 'SF',
            'name' => 'Small Forward',
            'type' => Type::BASKETBALL,
            'description' => 'Small Forward position in basketball',
        ]);
        Role::create([
            'id' => 'SG',
            'name' => 'Shooting Guard',
            'type' => Type::BASKETBALL,
            'description' => 'Shooting Guard position in basketball',
        ]);
        Role::create([
            'id' => 'PG',
            'name' => 'Point Guard',
            'type' => Type::BASKETBALL,
            'description' => 'Point Guard position in basketball',
        ]);

        Status::create([
            'id' => 'ACTIVE',
            'name' => 'Active',
            'description' => 'User is active',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'INACTIVE',
            'name' => 'Inactive',
            'description' => 'User is inactive',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'DEACTIVATED',
            'name' => 'Deactivated',
            'description' => 'User is deactivated',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'BANNED',
            'name' => 'Banned',
            'description' => 'User is banned',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'PENDING',
            'name' => 'Pending',
            'description' => 'User is pending approval',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'INVITED',
            'name' => 'Invited',
            'description' => 'User is invited',
            'type' => Type::GENERAL,
        ]);

        Status::create([
            'id' => 'SCHEDULED',
            'name' => 'Scheduled',
            'description' => 'Game is scheduled',
            'type' => Type::GAME,
        ]);

        Status::create([
            'id' => 'ONGOING',
            'name' => 'Ongoing',
            'description' => 'Game is ongoing',
            'type' => Type::GAME,
        ]);

        Status::create([
            'id' => 'COMPLETED',
            'name' => 'Completed',
            'description' => 'Game is completed',
            'type' => Type::GAME,
        ]);

        Status::create([
            'id' => 'CANCELLED',
            'name' => 'Cancelled',
            'description' => 'Game is cancelled',
            'type' => Type::GAME,
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
