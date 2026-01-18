<?php

namespace Database\Seeders;

use App\Models\ActivityArea;
use App\Models\Area;
use App\Models\Country;
use App\Models\Department;
use App\Models\Inspection;
use App\Models\Inspector;
use App\Models\Investor;
use App\Models\Letter;
use App\Models\Organization;
use App\Models\Progress;
use App\Models\Project;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlacesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country = Country::factory()->create();
        $states = State::factory()
            ->for($country)
            ->count(3)
            ->create();

        foreach ($states as $state) {
            $departments = Department::factory()
                ->for($state)
                ->count(2)
                ->create();

            foreach ($departments as $department) {
                $area = Area::factory()
                    ->for($department)
                    ->create();

                $organization = Organization::factory()->create();
                $investor = Investor::factory()
                    ->for($country)
                    ->for($organization)
                    ->count(2);
                User::factory()
                    ->for($organization)
                    ->create();

                Project::factory()
                    ->count(2)
                    ->has($investor)
                    ->for($state)
                    ->for($area)
                    ->for($department)
                    ->for($organization)
                    ->for($activityArea = ActivityArea::query()->first())
                    ->for($activityArea->activityTypes->first())
                    ->create()
                    ->each(function (Project $project) use ($organization) {
                        Letter::factory()
                            ->for($project)
                            ->count(2)
                            ->create();

                        $inspection = Inspection::factory()
                            ->for($organization)
                            ->create();
                        Inspector::factory()
                            ->for($organization)
                            ->for($inspection)
                            ->create();
                        foreach (range(5, 100, mt_rand(5, 20)) as $percentage) {
                            Progress::factory()
                                ->for($inspection)
                                ->for($project)
                                ->create([
                                    'progress_percentage' => $percentage,
                                    'visited_at' => now()->subMonths(100 - $percentage),
                                ]);
                        }
                    });
            }
        }
    }
}
