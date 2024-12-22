<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\Schedule;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 4 specializations
        // $specializations = Specialization::factory()->count(6)->create();

        // Create a superadmin
        $superadmin = User::factory()->state([
            'role' => 'superadmin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('12345678'),
        ])->create();

        // Create an admin
        $admin = User::factory()->state([
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
        ])->create();

        // Create a patient
        $patient = User::factory()->state([
            'role' => 'patient',
            'email' => 'patient@gmail.com',
            'password' => bcrypt('12345678'),
        ])->create();

        // Create a patient row in the patients table
        Patient::create([
            'user_id' => $patient->id
        ]);

        // Create a doctor
        $specializations = Specialization::all();
        $doctor = User::factory()->state([
            'role' => 'doctor',
            'email' => 'doctor@gmail.com',
            'password' => bcrypt('12345678'),

        ])->create();

        // Create a doctor row in the doctors table
        $doctorRecord = Doctor::create([
            'user_id' => $doctor->id,
            'specialization_id' => $specializations->random()->id, // Assign a random specialization
            'hourly_rate' => 500, // Default rate
            'bio' => 'This is a bio for the doctor.', // Default bio
        ]);

        // Create the doctor schedules
        $this->createDoctorSchedules($doctorRecord);
    }

        /**
     * Create schedules for the doctor.
     *
     * @param User $doctor
     * @return void
     */
    private function createDoctorSchedules(Doctor $doctor)
    {

        if ($doctor) {
            // Days of the week for schedule
            $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            foreach ($daysOfWeek as $day) {
                Schedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'start_time' => '10:00',
                    'end_time' => '17:00',
                    'slot_count' => 14,
                    'status' => 'available',
                ]);
            }
        }
    }
}
