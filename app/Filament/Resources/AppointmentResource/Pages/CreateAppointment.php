<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Payment;
use App\Services\AppointmentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): Notification
    {
        $record = $this->record;
        $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
        return Notification::make()
            ->success()
            ->icon('heroicon-o-calendar')
            ->title('Appointment Booked!')
            ->body("$text has been successfully booked.");
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if(Auth::user()->role === 'patient'){
            $data['patient_id'] = Auth::user()->patient->id;
            $appointmentService = app(AppointmentService::class);
            // $availableSlots = $appointmentService->generateAvailableSlots($doctor, $appointment_date);
        }
        return $data;

    }

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
