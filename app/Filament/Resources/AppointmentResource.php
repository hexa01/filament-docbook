<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Filament\Resources\AppointmentResource\RelationManagers\MessageRelationManager;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    protected static ?string $navigationGroup = 'Appointment Management';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';




    public static function form(Form $form): Form
    {

        return $form
            ->schema([

                Forms\Components\Section::make('Basic Appointment Information')
                    ->schema([
                        // Patient Selection
                        Select::make('patient_id')
                            ->label('Patient Name')
                            ->searchable()
                            ->preload()
                            ->options(
                                Patient::with('user')
                                    ->get()->pluck('user.name', 'id')
                            )
                            ->hidden(fn()=>Auth::user()->role === 'patient')
                            ->required(),
                            Forms\Components\Select::make('specialization_id')
                            ->label('Specialization')
                            ->options(fn() => Specialization::pluck('name', 'id')->toArray())
                            ->required()
                            // ->disabled(fn(callable $get) => $get('id') !== null)
                            ->live(),


                        // Doctor Selection
                        Select::make('doctor_id')
                            ->label('Doctor Name')
                            ->searchable()
                            ->options(function (callable $get) {
                                $specializationId = $get('specialization_id');
                                if (!$specializationId) {
                                    return [];
                                }
                                return Doctor::with('user')
                                    ->where('specialization_id', $specializationId)
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->required(),

                        // Appointment Date
                        DatePicker::make('appointment_date')
                            ->required()
                            ->live()
                            ->minDate(Carbon::tomorrow()),

                        // Available Slots
                        Select::make('slot')
                            ->label('Available Slots')
                            ->placeholder(function (callable $get) {
                                return $get('doctor_id') === null ? 'Select a slot' : 'No slots available';
                            })
                            ->options(function (callable $get) {
                                $doctor = Doctor::find($get('doctor_id'));
                                $appointment_date = $get('appointment_date');

                                if (!$doctor || !$appointment_date) {
                                    return [];
                                }

                                $appointmentService = app(AppointmentService::class);
                                $availableSlots = $appointmentService->generateAvailableSlots($doctor, $appointment_date);
                                return collect($availableSlots)
                                    ->mapWithKeys(fn($slot) => [$slot => $slot])
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),
                    ])->columns(2),
                Forms\Components\Section::make('Appointment Status')
                    ->schema([
                        Forms\Components\Select::make('status')

                            ->options([
                                'completed' => 'Completed',
                                'missed' => 'Missed',
                            ])
                            ->required(),
                    ])->columns(2)
                    ->hidden(fn($get) => !$get('record') || !$get('record.id')),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {

        return $infolist
            ->schema([
                Section::make('Basic Appointment Information')
                    ->schema([
                        TextEntry::make('patient.user.name')->label('Patient Name'),
                        TextEntry::make('doctor.user.name')->label('Doctor Name'),
                        TextEntry::make('appointment_date')->label('Appointment Date'),
                        TextEntry::make('slot')->label('Appointment Slot Time'),
                        TextEntry::make('status')->label('Appointment Status'),
                        // TextEntry::make('doctors_count')->label('Number of doctors for this specialization')
                    ])->columns(2),
                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('payment.status')->label('Payment Status'),
                        TextEntry::make('payment.payment_method')->label('Payment Method'),
                        // TextEntry::make('doctors_count')->label('Number of doctors for this specialization')
                    ])->columns(2)


            ]);
    }


    public static function getRelations(): array
    {
        return [
            MessageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }



    // public function getTabs(): array
    // {
    //     $today = Carbon::today();

    //     return [
    //         'All' => Tab::make(),
    //         'Today' => Tab::make()
    //             ->modifyQueryUsing(
    //                 fn(Builder $query) =>
    //                 $query->whereDate('appointment_date', '=', $today)
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '=', $today)->count()
    //             ),
    //         'This Week' => Tab::make()
    //             ->modifyQueryUsing(
    //                 fn(Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfWeek())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfWeek())->count()
    //             ),
    //         'This Month' => Tab::make()
    //             ->modifyQueryUsing(
    //                 fn(Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfMonth())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfMonth())->count()
    //             ),
    //         'This Year' => Tab::make()
    //             ->modifyQueryUsing(
    //                 fn(Builder $query) =>
    //                 $query->whereDate('appointment_date', '>=', $today->startOfYear())
    //             )
    //             ->badge(
    //                 Appointment::whereDate('appointment_date', '>=', $today->startOfYear())->count()
    //             ),
    //     ];
    // }

    // public static function canEdit(Model $record): bool
    // {

    //     return $record->status == 'pending';
    // }
}
