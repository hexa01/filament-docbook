<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function getCreatedNotification(): Notification
    // {
    //     $record = $this->record;

    //     return Notification::make()
    //         ->success()
    //         ->icon('heroicon-o-calendar')
    //         ->title('Appointment Booked!')
    //         ->body("Your appointment with Dr. {$record->doctor->user->name} for {$record->appointment_date} has been successfully booked.");
    // }

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
