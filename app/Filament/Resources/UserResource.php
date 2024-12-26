<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'User';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(fn(callable $get) => $get('id') == null)
                    ->required(),
                    Forms\Components\Select::make('gender')
                    ->label('Gender')
                    ->required()
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                    Forms\Components\DatePicker::make('dob')
                    ->label('Date of Birth')
                    ->required()
                    ->displayFormat('d/m/Y'),
                Forms\Components\Select::make('role')
                    ->required()
                    ->label('Role')
                    ->disabled(fn(callable $get) => $get('id') !== null)
                    ->options([
                        'doctor' => 'Doctor',
                        'patient' => 'Patient',
                        // 'admin' => 'Admin',
                    ])->reactive(),
                Forms\Components\Select::make('specialization_id')
                    ->label('Specialization')
                    ->options(fn() => Specialization::pluck('name', 'id')->toArray())
                    // ->disabled(fn(callable $get) => $get('id') !== null)
                    ->required()
                    // ->default(fn($record) => $record->specialization_id)
                    ->visible(fn($get) => $get('role') == 'doctor'),
                    Forms\Components\TextInput::make('hourly_rate')
                    ->required()
                    ->visible(fn($get) => $get('role') == 'doctor'),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('phone')
                ->unique(fn(callable $get) => $get('id') == null),
                // Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                ->label("New password")
                ->password()
                    ->password()
                    ->required(fn(callable $get) => $get('id') == null)
                    ->placeholder(function(callable $get){
                        if($get('id') !== null){
                            return 'Enter new password here only if you want to reset password';
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
