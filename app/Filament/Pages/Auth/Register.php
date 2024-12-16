<?php

namespace App\Filament\Pages\Auth;

use App\Models\Specialization;
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
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->email()
                            ->required(),
                        DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->required()
                            ->native(false)
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
                        TextInput::make('phone')
                            ->tel(),
                        // Forms\Components\DateTimePicker::make('email_verified_at'),
                        TextInput::make('password')
                            ->password()
                            ->required(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
