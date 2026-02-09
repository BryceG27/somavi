<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Pagamenti';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pagamento')
                    ->schema([
                        Forms\Components\Select::make('reservation_id')
                            ->label('Prenotazione')
                            ->relationship('reservation', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('provider')
                            ->label('Provider')
                            ->options([
                                'stripe' => 'Stripe',
                            ])
                            ->required(),
                        Forms\Components\Select::make('step')
                            ->label('Tranche')
                            ->options([
                                Payment::STEP_FULL => 'Totale',
                                Payment::STEP_DEPOSIT => 'Caparra',
                                Payment::STEP_BALANCE => 'Saldo',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Stato')
                            ->options([
                                Payment::STATUS_PENDING => 'In attesa',
                                Payment::STATUS_PAID => 'Pagato',
                                Payment::STATUS_REFUNDED => 'Rimborsato',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Importo')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('currency')
                            ->label('Valuta')
                            ->maxLength(3)
                            ->required(),
                        Forms\Components\DatePicker::make('due_at')
                            ->label('Scadenza'),
                        Forms\Components\TextInput::make('locale')
                            ->label('Locale')
                            ->maxLength(5),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Pagato il'),
                        Forms\Components\DateTimePicker::make('refunded_at')
                            ->label('Rimborsato il'),
                        Forms\Components\TextInput::make('stripe_checkout_session_id')
                            ->label('Stripe Session ID')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stripe_payment_intent_id')
                            ->label('Stripe Payment Intent ID')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stripe_refund_id')
                            ->label('Stripe Refund ID')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Importo rimborsato')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation.id')
                    ->label('Prenotazione')
                    ->sortable(),
                Tables\Columns\TextColumn::make('step')
                    ->label('Tranche')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Importo')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Scadenza')
                    ->date(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pagato')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creato')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
