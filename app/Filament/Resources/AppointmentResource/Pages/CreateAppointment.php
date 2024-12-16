<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;  // Access the created Appointment model
        $data = $this->form->getState();
        $price = 500;
        Payment::create([
            'appointment_id' => $record->id,
            'amount' => $price,
            'status' => 'unpaid',
        ]);
    }
}
