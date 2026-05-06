<?php

namespace Database\Seeders;

use App\Models\LocationType;
use App\Models\MasterJob;
use Illuminate\Database\Seeder;

class MasterJobSeeder extends Seeder
{
    public function run(): void
    {
        $toilet  = LocationType::where('name', 'Toilet')->first();
        $laktasi = LocationType::where('name', 'Laktasi')->first();

        $toiletJobs = [
            'Pengharum Ruangan Hidup & Berfungsi',
            'Kloset, urinoir bersih & Berfungsi',
            'Shower, gayung, ember bersih & berfungsi',
            'Pintu dan dinding pemisah Toilet bersih & berfungsi',
            'Lantai Toilet Kering & Wangi',
            'Wash Basin kering, bersih & berfungsi',
            'Kaca / Cermin toilet Bersih',
            'Tissue toilet tersedia',
            'Sabun cuci tangan tersedia',
            'Tempat sampah toilet dalam keadaan bersih',
            'Langit - langit toilet bersih dari jamur dan sarang laba-laba',
            'Ventilasi / Exshouse Bersih / Berfungsi',
            'Saklar lampu bersih dan berfungsi',
            'Keset toilet dalam keadaan bersih & kering',
            'Dinding toilet Bersih & kering',
            'Lantai koridor Toilet kering dan bersih',
            'Mushola ( lantai, dinding, atap & karpet ) dalam keadaan bersih & kering',
            'Lantai lobi, teras & kantor kering dan bersih',
            'Kaca jendela office kering dan bersih',
            'Tempat sampah luar keadaan Bersih & berfungsi',
            'Jenitor dalam keadaan bersih & Rapi',
            'Paving Bersih dari Gulma, Sampah basah & sampah Daun Kering',
            'Meja dan kursi dalam keadaan Bersih dan Tidak berdebu',
            'Atap / plavon bersih, bebas dari debu dan sarang laba-laba',
            'Ornamen Dalam Keadaan Bersih Tidak berdebu',
            'Westafel portabel, bersih, berfungsi, tersedia Handshoap',
        ];

        foreach ($toiletJobs as $i => $job) {
            MasterJob::create([
                'location_type_id' => $toilet->id,
                'job'              => $job,
                'order'            => $i + 1,
                'is_active'        => true,
            ]);
        }

        $laktasiJobs = [
            'Kursi / Sofa',
            'Meja',
            'Dinding Bersih & kering',
            'Laci',
            'Lantai Kering & bersih',
            'Wash Basin kering, bersih & berfungsi',
            'Kaca / Cermin Bersih',
            'Tissue tersedia',
            'Sabun cuci tangan tersedia',
            'Langit - langit bersih dari jamur dan sarang laba-laba',
            'Ventilasi / Exshouse Bersih / Berfungsi',
            'Saklar & lampu bersih dan berfungsi',
        ];

        foreach ($laktasiJobs as $i => $job) {
            MasterJob::create([
                'location_type_id' => $laktasi->id,
                'job'              => $job,
                'order'            => $i + 1,
                'is_active'        => true,
            ]);
        }
    }
}