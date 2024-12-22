<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Specialization;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
{
    // dd($data);
    $currentEditingUser = User::find($data['id']);
    // dd($currentEditingUser->doctor->specialization->name);

 
    if ($data['role'] == 'patient'){
        $data['gender'] = $currentEditingUser->patient->gender ?? null;
        $data['dob'] = $currentEditingUser->patient->dob ?? null;
    }
    if ($data['role'] == 'doctor'){
        $data['specialization_id'] = $currentEditingUser->doctor->specialization->id ?? null;
    }


    return $data;
}
}
