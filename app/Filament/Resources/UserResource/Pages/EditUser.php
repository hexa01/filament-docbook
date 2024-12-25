<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
{
    $currentEditingUser = User::find($data['id']);
    if ($data['role'] == 'doctor'){
        $data['specialization_id'] = $currentEditingUser->doctor->specialization->id ?? null;
        $data['hourly_rate'] = $currentEditingUser->doctor->hourly_rate ?? null;
        // $data['bio'] = $currentEditingUser->doctor->bio ?? null;
    }
    return $data;
}

protected function mutateFormDataBeforeSave(array $data): array
{
    if($data['password'] == null){
        unset($data['password']);
    }
    return $data;
}

protected function afterSave(): void
{
    $record = $this->record;  // Access the created User model
    $data = $this->form->getState();
    $currentEditingUser = User::find($record['id']);
    if ($currentEditingUser['role'] === 'doctor') {
        $doctor = Doctor::find($currentEditingUser->doctor->id);
        $doctor->update([
            'specialization_id' => $data['specialization_id'],
            'hourly_rate' => $data['hourly_rate'],
        ]);
    // return $data;
    }
}
}
