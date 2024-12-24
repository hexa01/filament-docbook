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
}
