<?php

namespace Database\Seeders;

use App\Models\ActivityArea;
use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activityAreas = ActivityArea::factory()->count(4)->create();

        foreach ($activityAreas as $area) {
            ActivityType::factory()
                ->for($area)
                ->count(2)
                ->create();
        }
    }
}
