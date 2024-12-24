<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action as Action;
use Filament\Tables\Actions\ActionGroup as ActionGroup;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Appointment Management';

    public static function form(Form $form): Form
    {
        abort(404);
        return $form
            ->schema([
                Forms\Components\TextInput::make('appointment_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('pid'),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = User::find(Filament::auth()->user()->id);
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


                TextColumn::make('status')
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
            ->defaultSort('appointment.appointment_date', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // Get the currently authenticated user
                $user = User::find(Filament::auth()->user()->id);

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

            //->url(fn ($record) => route('filament.admin.resources.payments.stripe', ['appointmentId' => $record->id]))



            ->actions([
                ActionGroup::make([
                    // Action::make('Pay with eSewa')
                    //     ->action(function ($record) {
                    //         // Update payment status to 'paid'
                    //         $record->status = 'paid';
                    //         $record->payment_method = 'esewa';
                    //         $record->save(); // Save the updated record

                    //         $appointment =  $record->appointment;
                    //         $appointment->status = 'booked';
                    //         $appointment->save();
                    //         return redirect()->route('filament.admin.resources.payments.index');

                    //         // return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                    //     })

                    //     ->icon('heroicon-o-currency-dollar')
                    //     ->color('success')
                    //     ->label('Pay via eSewa')
                    //     ->tooltip('Click to pay via eSewa'),
                    Action::make('Pay with Stripe')
                        ->url(function($record) {

                            $url = url('/admin/payments/stripe', ['payment' => $record]);
                            // dd($url);
                            return $url;
                        })
                        // ->action(function ($record) {
                        //     // Redirect to the CheckoutPage with payment details
                        //     return redirect()->route('filament.pages.checkout-page', [
                        //         'payment' => $record->id, // Pass payment ID
                        //     ]);
                        // })
                        ->icon('heroicon-o-currency-dollar')
                        // ->button()
                        ->color('secondary')
                        ->label('Pay via Stripe')
                        ->tooltip('Click to pay via Stripe')
                ])
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->hidden(fn() => $user->role === 'doctor')
                    ->label('Make Payment')
                    ->icon('heroicon-m-credit-card')
                    ->size(ActionSize::Small)
                    ->color('primary')
                    ->button(),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
        ;
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
            'index' => Pages\ListPayments::route('/'),
            'stripe' => Pages\PaymentPage::route('/stripe/{record}'),

            // 'create' => Pages\CreatePayment::route('/create'),
            // 'stripe' => Pages\StripePayment::route('/stripe/{appointmentId}'),
            // 'view' => Pages\ViewPayment::route('/{record}'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }
}
