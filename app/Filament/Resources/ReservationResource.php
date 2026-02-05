<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli prenotazione')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name.' <'.$record->email.'>')
                            ->required(),
                        Forms\Components\Select::make('apartment_id')
                            ->label('Appartamento')
                            ->relationship('apartment', 'name_it')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Stato')
                            ->options([
                                Reservation::STATUS_PENDING => 'In attesa',
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
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => $record->customer?->full_name ?? '-')
                    ->searchable(['customer.name', 'customer.surname', 'customer.email'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('apartment.name_it')
                    ->label('Appartamento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->colors([
                        'warning' => Reservation::STATUS_PENDING,
                        'success' => Reservation::STATUS_CONFIRMED,
                        'danger' => Reservation::STATUS_CANCELLED,
                    ]),
                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Persone')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Da')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('A')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Pagata')
                    ->boolean(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotale')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Sconto %')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Totale')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_paid')
                    ->label('Pagato')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'view' => Pages\ViewReservation::route('/{record}'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
