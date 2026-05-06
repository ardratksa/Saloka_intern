<?php

namespace Database\Seeders;

use App\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationTypeSeeder extends Seeder
{
    public function run(): void
    {
        LocationType::create(['name' => 'Toilet',  'is_active' => true]);
        LocationType::create(['name' => 'Laktasi', 'is_active' => true]);
    }
}