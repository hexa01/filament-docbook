<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        $user = User::find(Auth::user()->id);
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                    ->label('Patient Name')
                    ->sortable()
                    ->hidden(fn() => $user->role === 'patient'),
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                    ->label('Doctor Name')
                    ->sortable()
                    ->hidden(fn() => $user->role === 'doctor'),
                Tables\Columns\TextColumn::make('appointment.appointment_date')
                    ->label('Appointment Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pid')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),


                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        default => 'secondary'
                    }),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->default("-")
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('status', 'desc')
            // ->defaultSort('appointment.appointment_date', 'asc')
            ->modifyQueryUsing(function (Builder $query) {
                // Get the currently authenticated user
                $user = User::find(Auth::user()->id);

                // If the user is an admin, they can see all payments
                if ($user->hasRole('admin')) {
                    return $query;
                }

                // If the user is a doctor, only their appointment payments are shown
                if ($user->hasRole('doctor')) {
                    $appointments = Appointment::where('doctor_id', $user->doctor->id)->get();
                    if ($appointments->isNotEmpty()) {
                        $appointmentIds = $appointments->pluck('id');
                        return $query->whereIn('appointment_id', $appointmentIds);
                    }
                }

                // If the user is a patient, only their appointment payments are shown
                if ($user->hasRole('patient')) {
                    $appointments = Appointment::where('patient_id', $user->patient->id)->get();
                    if ($appointments->isNotEmpty()) {
                        $appointmentIds = $appointments->pluck('id');
                        return $query->whereIn('appointment_id', $appointmentIds);
                    }
                }
                //default
                return $query->whereRaw('1 = 0');
            })
            ->filters([
                //
            ])
            ->actions([
                    Action::make('Pay with Stripe')
                        ->url(function($record) {

                            $url = url('/admin/payments/stripe', ['payment' => $record]);
                            return $url;
                        })
                        ->label('Pay via Stripe')
                        ->tooltip('Click to pay via Stripe')
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->hidden(fn() => $user->role === 'doctor')
                    ->icon('heroicon-m-credit-card')
                    ->size(ActionSize::Small)
                    ->color('blue')
                    ->button(),
            ]);
    }
}
