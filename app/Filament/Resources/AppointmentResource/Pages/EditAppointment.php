<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // dd($data);
        $currentEditingAppointment = Appointment::find($data['id']);
            $data['doctor_id'] = $currentEditingAppointment->doctor_id ?? null;
            $data['specialization_id'] = $currentEditingAppointment->doctor->specialization->id ?? null;
        return $data;
    }
}
