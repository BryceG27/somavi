<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockedDateResource\Pages;
use App\Models\BlockedDate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlockedDateResource extends Resource
{
    protected static ?string $model = BlockedDate::class;

    protected static ?string $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?string $navigationLabel = 'Blocchi date';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Blocco')
                    ->schema([
                        Forms\Components\Select::make('apartment_id')
                            ->label('Appartamento')
                            ->relationship('apartment', 'name_it')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Da')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('A')
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->label('Motivo')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Da')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('A')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(30),
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
            'index' => Pages\ListBlockedDates::route('/'),
            'create' => Pages\CreateBlockedDate::route('/create'),
            'edit' => Pages\EditBlockedDate::route('/{record}/edit'),
        ];
    }
}
