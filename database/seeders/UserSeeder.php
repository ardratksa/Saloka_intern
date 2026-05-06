<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Admin',
            'email'     => 'admin@saloks.com',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_leader' => true,
            'wa_number' => '08111234567',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Leader Dua',
            'email'     => 'leader2@saloks.com',
            'password'  => bcrypt('password'),
            'role'      => 'admin',
            'is_leader' => true,
            'wa_number' => '08127654321',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Siti Rahayu',
            'email'     => 'siti@saloks.com',
            'password'  => bcrypt('password'),
            'role'      => 'staff',
            'is_leader' => false,
            'is_active' => true,
        ]);
    }
}