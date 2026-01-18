<?php

namespace Database\Seeders;

use App\Models\LicensingAuthority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LicensingAuthoritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'en' => 'DGIE',
            ],
            [
                'en' => 'DGID',
            ],
            [
                'en' => 'DGIS',
            ],
            [
                'en' => 'BOI',
            ],
            [
                'en' => 'BOI AND Governorates DGs',
            ],
        ];

        foreach ($data as $datum) {
            LicensingAuthority::create(['name' => $datum]);
        }
    }
}
