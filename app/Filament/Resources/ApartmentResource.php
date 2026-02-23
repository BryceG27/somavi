<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApartmentResource\Pages;
use App\Filament\Resources\ApartmentResource\RelationManagers\ApartmentAttachmentsRelationManager;
use App\Models\Apartment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApartmentResource extends Resource
{
    protected static ?string $model = Apartment::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dettagli appartamento')
                    ->schema([
                        Forms\Components\Tabs::make('Lingue')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Italiano')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_it')
                                            ->label('Nome')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('address_it')
                                            ->label('Indirizzo')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description_it')
                                            ->label('Descrizione')
                                            ->rows(5),
                                    ]),
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_en')
                                            ->label('Name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('address_en')
                                            ->label('Address')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description_en')
                                            ->label('Description')
                                            ->rows(5),
                                    ]),
                            ])
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('rooms_count')
                            ->label('Numero stanze')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('beds_count')
                            ->label('Numero letti')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('bathrooms_count')
                            ->label('Numero bagni')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_guests')
                            ->label('Numero massimo ospiti')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('check_in_text')
                            ->label('Check-in')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('check_out_text')
                            ->label('Check-out')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Hero')
                    ->schema([
                        Forms\Components\Tabs::make('Hero Lingue')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Italiano')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_kicker_it')
                                            ->label('Kicker')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('hero_headline_it')
                                            ->label('Titolo')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('hero_body_it')
                                            ->label('Testo')
                                            ->rows(4),
                                        Forms\Components\TextInput::make('hero_primary_cta_it')
                                            ->label('CTA principale')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('hero_secondary_cta_it')
                                            ->label('CTA secondaria')
                                            ->maxLength(255),
                                    ]),
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_kicker_en')
                                            ->label('Kicker')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('hero_headline_en')
                                            ->label('Title')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('hero_body_en')
                                            ->label('Text')
                                            ->rows(4),
                                        Forms\Components\TextInput::make('hero_primary_cta_en')
                                            ->label('Primary CTA')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('hero_secondary_cta_en')
                                            ->label('Secondary CTA')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Prezzi')
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
                Forms\Components\Section::make('Integrazioni')
                    ->schema([
                        Forms\Components\TextInput::make('airbnb_url')
                            ->label('Airbnb Listing URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('airbnb_ical_url')
                            ->label('Airbnb iCal URL')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\TextInput::make('booking_url')
                            ->label('Booking Listing URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('booking_ical_url')
                            ->label('Booking iCal URL')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\TextInput::make('vrbo_url')
                            ->label('Vrbo Listing URL')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('vrbo_ical_url')
                            ->label('Vrbo iCal URL')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\TextInput::make('airbnb_api_key')
                            ->label('Airbnb API Key')
                            ->maxLength(255)
                            ->password(),
                        Forms\Components\TextInput::make('booking_api_key')
                            ->label('Booking API Key')
                            ->maxLength(255)
                            ->password(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Contatti')
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Telefono')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('whatsapp_url')
                            ->label('WhatsApp URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_it')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_it')
                    ->label('Indirizzo')
                    ->limit(40),
                Tables\Columns\TextColumn::make('rooms_count')
                    ->label('Stanze')
                    ->sortable(),
                Tables\Columns\TextColumn::make('beds_count')
                    ->label('Letti')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_guests')
                    ->label('Ospiti')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aggiornato')
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

    public static function getRelations(): array
    {
        return [
            ApartmentAttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApartments::route('/'),
            'create' => Pages\CreateApartment::route('/create'),
            'view' => Pages\ViewApartment::route('/{record}'),
            'edit' => Pages\EditApartment::route('/{record}/edit'),
        ];
    }
}
