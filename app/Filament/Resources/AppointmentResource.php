<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Services\AppointmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        $appointmentService = app(AppointmentService::class); // Inject appointment service
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->label('Patient Name')
                    ->searchable()
                    ->preload()
                    ->options(
                        Patient::with('user')
                            ->get()->pluck('user.name','id')
                    )
                    ->required(),
                    // Forms\Components\Select::make('specialization_id')
                    // ->label('Specialization')
                    // ->options(fn() => Specialization::pluck('name', 'id')->toArray())
                    // ->required()
                    // ->preload()
                    // ->searchable()
                    // ->live(),
                    Forms\Components\Select::make('doctor_id')
                    ->label('Doctor Name')
                    ->searchable()
                    ->preload()
                    ->options(function (callable $get) {
                        $specializationId = $get('specialization_id');

                        return Doctor::
                        // where('specialization_id', $specializationId)
                        with('user')
                            // ->with('user')
                            ->get()
                            ->pluck('user.name', 'id');
                    })
                    ->required()
                    ->live(),



                // Forms\Components\TextInput::make('doctor_id')
                //     ->required()
                //     ->numeric(),
                Forms\Components\DatePicker::make('appointment_date')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                ->label('Available Slots')
                    ->required(),
                // Forms\Components\TextInput::make('status')
                //     ->required(),
                // Forms\Components\Textarea::make('doctor_message')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
