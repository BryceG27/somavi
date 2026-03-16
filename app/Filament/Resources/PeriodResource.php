<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PeriodResource\Pages;
use App\Models\Period;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PeriodResource extends Resource
{
    protected static ?string $model = Period::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Periodi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periodo')
                    ->schema([
                        Forms\Components\Select::make('apartment_id')
                            ->label('Appartamento')
                            ->relationship('apartment', 'name_it')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome periodo')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Da')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('A')
                            ->required()
                            ->rule('after:start_date'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Prezzi per notte')
                    ->schema([
                        Forms\Components\TextInput::make('base_price')
                            ->label('Prezzo base (1 ospite)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€')
                            ->required(),
                        Forms\Components\TextInput::make('extra_guest_price_2')
                            ->label('Supplemento 2° ospite')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                        Forms\Components\TextInput::make('extra_guest_price_3')
                            ->label('Supplemento 3° ospite')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                        Forms\Components\TextInput::make('extra_guest_price_4')
                            ->label('Supplemento 4° ospite')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('apartment.name_it')
                    ->label('Appartamento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome periodo')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Da')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('A')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Base')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato')
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
            'index' => Pages\ListPeriods::route('/'),
            'create' => Pages\CreatePeriod::route('/create'),
            'edit' => Pages\EditPeriod::route('/{record}/edit'),
        ];
    }
}
