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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';




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
                            ->live()
                            ,


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
                        Select::make('start_time')
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

    // public static function table(Table $table): Table
    // {
    //     $user = User::find(Auth::user()->id);
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('patient.user.name')
    //                 ->searchable()
    //                 ->label("Patient's Name")
    //                 ->sortable()
    //                 ->hidden(fn() => $user->role === 'patient'),
    //             Tables\Columns\TextColumn::make('doctor.user.name')
    //                 ->searchable()
    //                 ->label("Doctor's Name")
    //                 ->sortable()
    //                 ->hidden(fn() => $user->role === 'doctor'),
    //             Tables\Columns\TextColumn::make('appointment_date')
    //                 ->date()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('start_time')
    //                 ->label("Slot"),
    //             Tables\Columns\TextColumn::make('status')
    //                 ->searchable(),
    //             Tables\Columns\TextColumn::make('payment.status')
    //                 ->label('Payment Status')
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //             Tables\Columns\TextColumn::make('updated_at')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //         ])->defaultSort('appointment_date')
    //         ->modifyQueryUsing(function (Builder $query) {
    //             // Get the currently authenticated user
    //             $user = User::find(Filament::auth()->user()->id);

    //             // If the user is an admin, they can see all appointments
    //             if ($user->hasRole('admin')) {
    //                 return $query;
    //             }

    //             // If the user is a doctor, only their appointments are shown
    //             if ($user->hasRole('doctor')) {
    //                 return $query->where('doctor_id', $user->doctor->id);
    //             }

    //             // If the user is a patient, only their appointments are shown
    //             if ($user->hasRole('patient')) {

    //                 if ($user->patient) {
    //                     return $query->where('patient_id', $user->patient->id);
    //                 } else {
    //                     return $query->whereRaw('1 = 0'); // If no patient relationship, show no appointments
    //                 }
    //             }

    //             //default
    //             return $query->whereRaw('1 = 0');
    //         })
    //         ->filters([
    //             Filter::make('appointment_date')
    //                 ->label('Appointment Date')
    //                 ->form([
    //                     DatePicker::make('appointment_date')
    //                 ])->query(function (Builder $query, array $data): Builder {
    //                     return $query
    //                         ->when(
    //                             $data['appointment_date'],
    //                             fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '=', $date),
    //                         );
    //                 })
    //                 ->indicateUsing(function (array $data): ?string {
    //                     if (! isset($data['date']) || ! $data['date']) {
    //                         return null;
    //                     }

    //                     // Display the selected date in a user-friendly format
    //                     return 'Appointment on ' . Carbon::parse($data['date'])->toFormattedDateString();
    //                 }),

    //             SelectFilter::make('status')
    //                 ->options([
    //                     'pending' => 'Pending',
    //                     'booked' => 'Booked',
    //                     'completed' => 'Completed',
    //                     'missed' => 'Missed',
    //                 ]),
    //         ])

    //         ->actions([
    //             Tables\Actions\ViewAction::make(),
    //             Tables\Actions\DeleteAction::make()
    //                 ->before(function ($record, DeleteAction $action) {
    //                     if (Auth::user()->role === 'admin' && $record->status === 'completed') {
    //                         Notification::make()
    //                             ->danger()
    //                             ->title('Appointment not deleted')
    //                             ->body('You can\'t delete appointment that is already completed.')
    //                             ->send();
    //                         $action->cancel();
    //                     }
    //                     if ($record->status === 'pending') {
    //                         Notification::make()
    //                         ->danger()
    //                         ->title('Appointment not deleted')
    //                         ->body('You can\'t delete appointment that is already booked.')
    //                         ->send();
    //                         $action->cancel();
    //                     }
    //                 })
    //                 ->successNotification(function ($record) {
    //                     return Notification::make()
    //                         ->success()
    //                         ->icon('heroicon-o-trash')
    //                         ->title('Appointment Removed!')
    //                         ->body("The appointment with Dr. {$record->doctor->user->name} for {$record->patient->user->name} on {$record->appointment_date} has been removed.");
    //                 }),

    //             //   if put action put it inside actionGroup
    //             //     Action::make('review')
    //             //         ->label('Leave a Review')
    //             //         ->color('teal')
    //             //         ->icon('heroicon-o-pencil-square')
    //             //         ->visible(fn($record) => $record->status === 'completed' && !$record->review)
    //             //         ->url(fn($record) => route('filament.admin.resources.reviews.create', ['appointment_id' => $record->id])),
    //             //     Action::make('view_review')
    //             //         ->label('View Review')
    //             //         ->color('indigo')
    //             //         ->icon('heroicon-o-eye')
    //             //         ->visible(fn($record) => $record->review)
    //             //         ->url(fn($record) => route('filament.admin.resources.reviews.view', ['record' => $record->review])),
    //             ActionGroup::make([
    //                 Action::make('markCompleted')
    //                     ->label('Mark as Completed')
    //                     ->icon('heroicon-s-check-circle')
    //                     ->hidden(fn() => $user->role === 'patient')
    //                     ->disabled(fn($record) => $record->status === 'completed' || $record->status === 'missed')
    //                     ->color(function($record){
    //                         if ($record->status === 'completed' || $record->status === 'missed'){
    //                             return 'gray';
    //                         }
    //                         return 'success';
    //                     })

    //                     ->action(function ($record) {
    //                         $record->update(['status' => 'completed']);

    //                         $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
    //                         Notification::make()
    //                             ->title('Appointment Completed')
    //                             ->success()
    //                             ->body("$text has been marked as completed.")
    //                             ->send();
    //                     }),
    //                 Action::make('markMissed')
    //                     ->label('Mark as Missed')
    //                     ->icon('heroicon-s-x-circle')
    //                     ->hidden(fn() => $user->role === 'patient')
    //                     ->disabled(fn($record) => $record->status === 'completed' || $record->status === 'missed')
    //                     ->action(function ($record) {
    //                         $record->update(['status' => 'missed']);
    //                         $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
    //                         Notification::make()
    //                             ->title('Appointment Marked as Missed')
    //                             ->success()
    //                             ->body("$text has been marked as missed.")
    //                             ->send();
    //                     })
    //                     ->color(function($record){
    //                         if ($record->status === 'completed' || $record->status === 'missed'){
    //                             return 'gray';
    //                         }
    //                         return 'danger';
    //                     }),
    //                 Tables\Actions\EditAction::make()
    //                 ->hidden(fn($record) =>  Auth::user()->role === 'admin' && $record->status === 'completed')
    //                 ->hidden(fn($record) => Auth::user()->role === 'patient' && $record->status !== 'pending')
    //                     ->label('Edit Appointment'),
    //             ])

    //                 ->tooltip('More Actions')
    //                 ->label('More Actions')
    //                 ->icon('heroicon-s-cog')
    //                 ->size(ActionSize::ExtraLarge)
    //                 ->color('primary')
    //             // ->button()
    //         ])
    //         ->bulkActions([
    //             BulkAction::make('markCompleted')
    //                 ->label('Mark as Completed')
    //                 ->color('success')
    //                 ->icon('heroicon-s-check-circle')
    //                 ->action(function (array $records) {
    //                     foreach ($records as $record) {
    //                         // Update each record to "completed"
    //                         $record->update(['status' => 'completed']);
    //                     }
    //                     Notification::make()
    //                         ->title('Appointments Status Updated')
    //                         ->success()
    //                         ->body("Selected Appointments have been marked as completed.")
    //                         ->send();
    //                 }),
    //             BulkAction::make('markMissed')
    //                 ->label('Mark as Missed')
    //                 ->color('danger')
    //                 ->icon('heroicon-s-x-circle')
    //                 ->action(function (array $records) {
    //                     foreach ($records as $record) {
    //                         // Update each record to "missed"
    //                         $record->update(['status' => 'missed']);
    //                     }

    //                     // Send notification after updating statuses
    //                     Notification::make()
    //                         ->title('Appointments Status Updated')
    //                         ->success()
    //                         ->body("Selected Appointments have been marked as missed.")
    //                         ->send();
    //                 }),

    //         ]);
    // }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Basic Appointment Information')
                    ->schema([
                        TextEntry::make('patient.user.name')->label('Patient Name'),
                        TextEntry::make('doctor.user.name')->label('Doctor Name'),
                        TextEntry::make('appointment_date')->label('Appointment Date'),
                        TextEntry::make('start_time')->label('Appointment Slot Time'),
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
