<?php

namespace Database\Seeders;

use App\Models\LocationName;
use App\Models\LocationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationNameSeeder extends Seeder
{
    public function run(): void
    {
        $toilet  = LocationType::where('name', 'Toilet')->first();
        $laktasi = LocationType::where('name', 'Laktasi')->first();

        $toilets = [
            'Toilet Office Big Shop',
            'Toilet Office Lama',
            'Toilet Mes Oyyo',
            'Toilet Lahan 2',
            'Toilet MK',
            'Toilet Personal Building',
            'Ruang Bilas',
            'Toilet Down Town',
            'Toilet Pesisir',
            'Toilet Resi',
            'Toilet Joglo',
            'Toilet Rimba Resto',
            'Toilet Araria',
            'Toilet Kamayayi',
            'Toilet Daimami',
        ];

        foreach ($toilets as $name) {
            LocationName::create([
                'location_type_id' => $toilet->id,
                'name'             => $name,
                'qr_code'          => (string) Str::uuid(),
                'is_active'        => true,
            ]);
        }

        $laktasis = [
            'Laktasi A',
            'Laktasi B',
            'Laktasi C',
            'Laktasi D',
        ];

        foreach ($laktasis as $name) {
            LocationName::create([
                'location_type_id' => $laktasi->id,
                'name'             => $name,
                'qr_code'          => (string) Str::uuid(),
                'is_active'        => true,
            ]);
        }
    }
}