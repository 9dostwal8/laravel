<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::factory()->create();

        User::factory()
            ->for($organization)
            ->create([
                'name' => 'Mehran',
                'email' => 'mehran.rasulian@gmail.com',
            ]);

        User::factory()
            ->for($organization)
            ->create([
                'name' => 'Salam',
                'email' => 'salam@boi-krd.org',
            ]);

        $this->call(LicensingAuthoritySeeder::class);
        $this->call(ActivityAreaSeeder::class);
        $this->call(PlacesSeeder::class);
        $this->call(ShieldSeeder::class);
    }
}
