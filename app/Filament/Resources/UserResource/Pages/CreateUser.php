<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;  // Access the created User model
        $data = $this->form->getState();
        if ($data['role'] === 'doctor') {
            $doctor = Doctor::create([
                'user_id' => $record->id,
                'specialization_id' => $data['specialization_id'],
                'hourly_rate' => $data['hourly_rate'],
            ]);

            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($days as $day) {
                Schedule::create([
                    'doctor_id' => $doctor->id,
                    'day' => $day,
                    'start_time' => '10:00',
                    'end_time' => '17:00',
                    'slot_count' => 14,
                    // 'status' => 'available',
                ]);
            }


        } elseif ($data['role'] === 'patient') {
            Patient::create([
                'user_id' => $record->id,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
