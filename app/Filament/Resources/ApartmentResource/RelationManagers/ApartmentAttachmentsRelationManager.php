<?php

namespace App\Filament\Resources\ApartmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ApartmentAttachment;

class ApartmentAttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attachment_type')
                    ->label('Tipo')
                    ->options([
                        ApartmentAttachment::TYPE_IMAGE => 'Immagine',
                        ApartmentAttachment::TYPE_VIDEO => 'Video',
                        ApartmentAttachment::TYPE_DOCUMENT => 'Documento',
                    ])
                    ->required()
                    ->default(ApartmentAttachment::TYPE_IMAGE)
                    ->live(),
                Forms\Components\FileUpload::make('path')
                    ->label('File')
                    ->disk('public')
                    ->directory('apartments')
                    ->acceptedFileTypes([
                        'image/*',
                        'video/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_cover')
                    ->label('Copertina'),
                Forms\Components\Toggle::make('is_enabled')
                    ->label('Attiva')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Immagine')
                    ->disk('public')
                    ->getStateUsing(fn ($record) => $record->attachment_type === ApartmentAttachment::TYPE_IMAGE ? $record->path : null),
                Tables\Columns\TextColumn::make('attachment_type')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_cover')
                    ->label('Copertina')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Attiva')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordine')
                    ->sortable(),
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
