<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApartmentAttachment extends Model
{
    use HasFactory;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_DOCUMENT = 'document';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'apartment_id',
        'path',
        'attachment_type',
        'is_cover',
        'is_enabled',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_cover' => 'bool',
        'is_enabled' => 'bool',
        'sort_order' => 'int',
    ];

    protected static function booted(): void
    {
        static::saving(function (ApartmentAttachment $attachment): void {
            if ($attachment->attachment_type !== self::TYPE_IMAGE) {
                $attachment->is_cover = false;
            }

            if (! $attachment->is_enabled && $attachment->is_cover) {
                $attachment->is_cover = false;
            }

            if ($attachment->is_cover) {
                $attachment->is_enabled = true;
            }
        });

        static::saved(function (ApartmentAttachment $attachment): void {
            if (! $attachment->is_cover) {
                return;
            }

            static::query()
                ->where('apartment_id', $attachment->apartment_id)
                ->where('id', '!=', $attachment->id)
                ->where('is_cover', true)
                ->update(['is_cover' => false]);
        });
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }
}
