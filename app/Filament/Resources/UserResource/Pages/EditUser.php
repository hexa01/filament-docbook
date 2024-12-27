<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

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
            Actions\DeleteAction::make()
            ->label('Delete User')
            ->before(function ($record, DeleteAction $action) {
                if($record->role == 'admin'){
                    Notification::make()
                    ->danger()
                    ->title('User not deleted')
                    ->body('You can\'t delete an admin user')
                    ->send();
                $action->cancel();
                }
                if($record->role == 'doctor'){
                    if ($record->doctor->appointments()->where('date', '>', Carbon::now())->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('User not deleted')
                            ->body('Please manage the pending and booked appointments of this doctor first.')
                            ->send();
                        $action->cancel();
                    }
                }

            })
            ->successNotification(function ($record) {
                return Notification::make()
                    ->danger()
                    ->icon('heroicon-o-trash')
                    ->title('User Removed!')
                    ->body("$record->name's account has been deleted.");
            }),
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
    $user = $this->record;

    $data['email'] = strtolower($data['email']);
    if (isset($data['email']) && $data['email'] !== strtolower($user->email)) {
        // Check if the new email already exists in the database
        $existingEmail = User::where('email', $data['email'])->exists();
        if ($existingEmail) {
            Notification::make()
            ->danger()
            ->title('Email Address Already In Use')
            ->body('The email address you entered is already associated with another account.')
            ->send();
            throw ValidationException::withMessages([
                'email' => 'This email address is already in use.',
            ]);

        }
    }

    if (isset($data['phone']) && $data['phone'] !== $user->phone) {
        // Check if the new phone number already exists in the database
        $existingPhone = User::where('phone', $data['phone'])->exists();
        if ($existingPhone) {
            Notification::make()
            ->danger()
            ->title('Phone Number Already In Use')
            ->body('The phone number you entered is already associated with another account.')
            ->send();

        // Throw a validation error to cancel the save action
        throw ValidationException::withMessages([
            'phone' => 'This phone number is already in use.',
        ]);
        }
    }



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
