<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialization;
use App\Models\Doctor;
use App\Models\Schedule;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SpecializationSeeder::class);
        $this->call(UserSeeder::class);

    }
}
