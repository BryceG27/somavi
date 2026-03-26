<?php

namespace App\Filament\Resources\ApartmentResource\RelationManagers;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ApartmentAttachment;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

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
                    ->disk('public_root')
                    ->directory('apartments')
                    ->acceptedFileTypes([
                        'image/*',
                        'video/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ])
                    ->maxSize((int) config('uploads.apartment_image_max_kb', 20480))
                    ->helperText('Le immagini vengono convertite automaticamente in formato WebP.')
                    ->saveUploadedFileUsing(static function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                        try {
                            if (! $file->exists()) {
                                return null;
                            }
                        } catch (UnableToCheckFileExistence) {
                            return null;
                        }

                        $extension = strtolower((string) $file->getClientOriginalExtension());
                        $mimeType = strtolower((string) $file->getMimeType());
                        $isRasterImage = str_starts_with($mimeType, 'image/')
                            && ! in_array($extension, ['svg', 'gif'], true);

                        if ($isRasterImage) {
                            $directory = trim((string) $component->getDirectory(), '/');
                            $generatedName = (string) $component->getUploadedFileNameForStorage($file);
                            $baseName = pathinfo($generatedName, PATHINFO_FILENAME);
                            $baseName = $baseName !== '' ? $baseName : (string) Str::ulid();
                            $optimizedPath = trim($directory . '/' . $baseName . '.webp', '/');

                            try {
                                $image = Image::read($file->getRealPath())
                                    ->orient()
                                    ->scaleDown(
                                        width: (int) config('uploads.apartment_image_max_width', 2560),
                                        height: (int) config('uploads.apartment_image_max_height', 2560),
                                    );

                                $encoded = $image->toWebp((int) config('uploads.apartment_image_webp_quality', 82));

                                $options = $component->getVisibility()
                                    ? ['visibility' => $component->getVisibility()]
                                    : [];

                                $component->getDisk()->put($optimizedPath, (string) $encoded, $options);

                                return $optimizedPath;
                            } catch (Throwable) {
                                // Fallback to standard storage behavior if optimization fails.
                            }
                        }

                        if (
                            $component->shouldMoveFiles() &&
                            ($component->getDiskName() === (fn (): string => $this->disk)->call($file))
                        ) {
                            $newPath = trim($component->getDirectory() . '/' . $component->getUploadedFileNameForStorage($file), '/');

                            $component->getDisk()->move((fn (): string => $this->path)->call($file), $newPath);

                            return $newPath;
                        }

                        $storeMethod = $component->getVisibility() === 'public'
                            ? 'storePubliclyAs'
                            : 'storeAs';

                        return $file->{$storeMethod}(
                            $component->getDirectory(),
                            $component->getUploadedFileNameForStorage($file),
                            $component->getDiskName(),
                        );
                    })
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
                    ->disk('public_root')
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
