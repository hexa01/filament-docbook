<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EditProfile extends BaseEditProfile
{
    public $mode = 'profile';

    public function mount(): void
    {
        $this->mode = request()->get('mode', 'profile');
        if ($this->mode == 'profile') {
            $this->fillForm();
        }
    }

    public static function getLabel(): string
    {
        if(request()->get('mode') == 'password') {
            return 'Change Password';
        }
        else {
            return 'Edit Profile';
        }
    }


    protected function getFormActions(): array
    {
        if ($this->mode == 'profile') {

            return [
                $this->getSaveFormAction()->label("Update General Information"),
                $this->getCancelFormAction(),
            ];
        }
        if ($this->mode == 'password') {
            return [
                $this->getSaveFormAction()->label("Change Password"),
                $this->getCancelFormAction(),
            ];
        }
    }
    protected function getForms(): array
    {
        // dd($this->maxWidth);
        $this->maxWidth = 'xl';
        if ($this->mode == 'profile') {
            return [
                'form' => $this->form(
                    $this->makeForm()
                        ->schema([
                            Section::make('General Information')
                                ->schema([
                                    $this->getNameFormComponent(),
                                    $this->getEmailFormComponent(),
                                    DatePicker::make('dob')
                                        ->label('Date of Birth')
                                        ->required()
                                        ->displayFormat('d/m/Y'),
                                    TextInput::make('address'),
                                    TextInput::make('phone'),
                                ])
                                ->columns(1),
                        ])
                        ->statePath('data'),
                ),
            ];
        }

        if ($this->mode == 'password') {
            return [
                'form' => $this->form(
                    $this->makeForm()
                        ->schema([
                            Section::make('Password Management')
                                ->schema([
                                    TextInput::make('old_password')
                                        ->password()
                                        ->revealable()
                                        ->label('Old Password'),
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent()
                                    ->visible(),
                                ])
                                ->columns(1),
                        ])
                        ->statePath('data'),
                ),
            ];
        }
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();
        if ($this->mode == 'profile') {


            $data['email'] = strtolower($data['email']);
            if (isset($data['email']) && $data['email'] !== $user->email) {
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
        }

        if ($this->mode == 'password') {
            if (isset($data['old_password']) && !Hash::check($data['old_password'], $user->password)) {
                // If the old password does not match the current password, throw a validation error
                Notification::make()
                    ->danger()
                    ->title('Incorrect Old Password')
                    ->body('The old password you entered is incorrect.')
                    ->send();

                // Throw validation error if old password is wrong
                throw ValidationException::withMessages([
                    'old_password' => 'The old password you entered is incorrect.',
                ]);
            }
        }
        return $data;
    }

    protected function getSavedNotification(): Notification
    {
        if($this->mode == 'password'){
        return Notification::make()
            ->success()
            ->title('Password Changed Successfully');
        }
        if($this->mode == 'profile'){
        return Notification::make()
            ->success()
            ->title('Profile Updated Successfully');
        }
    }
    protected function afterSave()
    {
        return redirect()->route('filament.admin.pages.dashboard');
    }
}
