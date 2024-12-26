<?php

namespace App\Filament\Pages\Auth;

use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;

use Filament\Pages\Page;

class Register extends BaseRegister
{
    protected function getForms(): array
    {

        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        DatePicker::make('dob')
                        ->label('Date of Birth')
                        ->required()
                        // ->native(false)
                        ->displayFormat('d/m/Y'),
                    Select::make('gender')
                        ->label('Gender')
                        ->required()
                        ->options([
                            'male' => 'Male',
                            'female' => 'Female',
                            'other' => 'Other',
                        ]),
                    TextInput::make('address'),
                    TextInput::make('phone'),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),

                        // Forms\Components\DateTimePicker::make('email_verified_at'),
                    ])
                    ->statePath('data'),
            ),
        ];
    }


    protected function afterRegister()
    {
        $user = $this->form->model;
        $data = $this->data;
            $patient = Patient::create([
                'user_id' => $user->id,
            ]);


    }
}
