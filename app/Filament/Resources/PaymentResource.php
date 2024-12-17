<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Payment;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Appointment Management';

    public static function form(Form $form): Form
    {
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                    ->label('Patient Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                    ->label('Doctor Name')
                    ->sortable(),
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
            ])
            ->filters([
                //
            ])



            //         Action::make('Pay via Stripe')
            //             ->url(fn ($record) => route('filament.admin.resources.payments.stripe', ['appointmentId' => $record->id]))
            //             ->icon('heroicon-o-currency-dollar')
            //             ->visible(fn ($record) => $record->payment_status !== 'paid')
            //             // ->button()
            //             ->color('secondary')
            //             ->label('Pay via Stripe')
            //             ->tooltip('Click to pay via Stripe')
            //         ])


            ->actions([
                ActionGroup::make([
                    Action::make('Pay with eSewa')
                        ->action(function ($record) {
                            // Update payment status to 'paid'
                            $record->status = 'paid';
                            $record->payment_method = 'esewa';
                            $record->save(); // Save the updated record

                            $appointment =  $record->appointment;
                            $appointment->status = 'booked';
                            $appointment->save();
                            return redirect()->route('filament.admin.resources.payments.index');

                            // return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn($record) => $record->status !== 'paid')
                        ->color('success')
                        ->label('Pay via eSewa')
                        ->tooltip('Click to pay via eSewa'),
                    Action::make('Pay with Stripe')
                        ->action(function ($record) {
                            // Update payment status to 'paid'
                            $record->status = 'paid';
                            $record->payment_method = 'stripe';
                            $record->save(); // Save the updated record
                            $appointment =  $record->appointment;
                            $appointment->status = 'booked';
                            $appointment->save();
                            return redirect()->route('filament.admin.resources.payments.index');
                            // return redirect()->route('payment.esewa', ['appointmentId' => $record->appointment_id]);
                        })
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn($record) => $record->status !== 'paid')
                        // ->button()
                        ->color('secondary')
                        ->label('Pay via Stripe')
                        ->tooltip('Click to pay via Stripe')
                ])
                    ->label('Make Payment')
                    ->icon('heroicon-m-credit-card')
                    ->size(ActionSize::Small)
                    ->color('purple')
                    ->button(),
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreatePayment::route('/create'),
            // 'stripe' => Pages\StripePayment::route('/stripe/{appointmentId}'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    // public static function canCreate(): bool
    // {
    //     return false;
    // }
}
