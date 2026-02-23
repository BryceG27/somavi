<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('apartment_id')
                    ->label('Appartamento')
                    ->relationship('apartment', 'name_it')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options([
                        Reservation::STATUS_AWAITING_PAYMENT => 'In attesa di pagamento',
                        Reservation::STATUS_PENDING => 'In verifica disponibilita',
                        Reservation::STATUS_CONFIRMED => 'Confermata',
                        Reservation::STATUS_CANCELLED => 'Cancellata',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('guests_count')
                    ->label('Numero persone')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Da')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('A')
                    ->required(),
                Forms\Components\Toggle::make('is_paid')
                    ->label('Pagato completamente'),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotale')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
                Forms\Components\TextInput::make('discount_percent')
                    ->label('Sconto %')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(0)
                    ->maxValue(100),
                Forms\Components\TextInput::make('total')
                    ->label('Totale')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
                Forms\Components\TextInput::make('total_paid')
                    ->label('Importo totale pagato')
                    ->numeric()
                    ->prefix('€')
                    ->minValue(0),
                Forms\Components\Textarea::make('notes')
                    ->label('Note')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apartment.name_it')
                    ->label('Appartamento'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'gray' => Reservation::STATUS_AWAITING_PAYMENT,
                        'warning' => Reservation::STATUS_PENDING,
                        'success' => Reservation::STATUS_CONFIRMED,
                        'danger' => Reservation::STATUS_CANCELLED,
                    ]),
                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Persone'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Da')
                    ->date(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('A')
                    ->date(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Pagata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Totale')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Pagato')
                    ->money('EUR'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
