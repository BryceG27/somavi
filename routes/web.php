<?php

use App\Models\Apartment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    $apartment = Apartment::query()
        ->with(['attachments' => function ($query) {
            $query
                ->where('is_enabled', true)
                ->orderBy('sort_order');
        }])
        ->first();

    if (! $apartment) {
        return Inertia::render('Home', ['apartment' => null]);
    }

    $cover = $apartment->attachments
        ->firstWhere('is_cover', true);

    if (! $cover || $cover->attachment_type !== 'image') {
        $cover = $apartment->attachments
            ->firstWhere('attachment_type', 'image');
    }

    return Inertia::render('Home', [
        'apartment' => [
            'id' => $apartment->id,
            'name_it' => $apartment->name_it,
            'name_en' => $apartment->name_en,
            'address_it' => $apartment->address_it,
            'address_en' => $apartment->address_en,
            'description_it' => $apartment->description_it,
            'description_en' => $apartment->description_en,
            'hero_kicker_it' => $apartment->hero_kicker_it,
            'hero_kicker_en' => $apartment->hero_kicker_en,
            'hero_headline_it' => $apartment->hero_headline_it,
            'hero_headline_en' => $apartment->hero_headline_en,
            'hero_body_it' => $apartment->hero_body_it,
            'hero_body_en' => $apartment->hero_body_en,
            'hero_primary_cta_it' => $apartment->hero_primary_cta_it,
            'hero_primary_cta_en' => $apartment->hero_primary_cta_en,
            'hero_secondary_cta_it' => $apartment->hero_secondary_cta_it,
            'hero_secondary_cta_en' => $apartment->hero_secondary_cta_en,
            'rooms_count' => $apartment->rooms_count,
            'beds_count' => $apartment->beds_count,
            'bathrooms_count' => $apartment->bathrooms_count,
            'max_guests' => $apartment->max_guests,
            'check_in_text' => $apartment->check_in_text,
            'check_out_text' => $apartment->check_out_text,
            'contact_email' => $apartment->contact_email,
            'contact_phone' => $apartment->contact_phone,
            'whatsapp_url' => $apartment->whatsapp_url,
            'airbnb_url' => $apartment->airbnb_url,
            'booking_url' => $apartment->booking_url,
            'cover_image_url' => $cover ? Storage::disk('public')->url($cover->path) : null,
            'images' => $apartment->attachments
                ->where('attachment_type', 'image')
                ->values()
                ->map(fn ($attachment) => [
                    'id' => $attachment->id,
                    'url' => Storage::disk('public')->url($attachment->path),
                    'is_cover' => $attachment->is_cover,
                    'sort_order' => $attachment->sort_order,
                ]),
        ],
    ]);
});
