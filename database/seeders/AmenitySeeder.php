<?php

namespace Database\Seeders;

use App\Models\ActivityArea;
use App\Models\Amenity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (ActivityArea::query()->take(5)->get() as $activityArea) {
            Amenity::factory()
                ->for($activityArea)
                ->create();
        }
    }
}
