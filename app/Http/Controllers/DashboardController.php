<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Payment;
use App\Models\Reservation;
use App\Support\LocalePreference;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $apartment = Apartment::query()
            ->with(['attachments' => function ($query) {
                $query
                    ->where('is_enabled', true)
                    ->orderBy('sort_order');
            }])
            ->first();

        if (! $apartment) {
            return Inertia::render('Home', [
                'apartment' => null,
                'auth' => [
                    'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'surname', 'email', 'preferred_locale']) : null,
                ],
                'blocked_dates' => [],
                'reservations' => [],
                'app_url' => rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/'),
            ]);
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
                'vrbo_url' => $apartment->vrbo_url,
                'base_price' => $apartment->base_price,
                'extra_guest_price_2' => $apartment->extra_guest_price_2,
                'extra_guest_price_3' => $apartment->extra_guest_price_3,
                'extra_guest_price_4' => $apartment->extra_guest_price_4,
                'cover_image_url' => $cover ? Storage::disk('public_root')->url($cover->path) : null,
                'images' => $apartment->attachments
                    ->where('attachment_type', 'image')
                    ->values()
                    ->map(fn ($attachment) => [
                        'id' => $attachment->id,
                        'url' => Storage::disk('public_root')->url($attachment->path),
                        'is_cover' => $attachment->is_cover,
                        'sort_order' => $attachment->sort_order,
                    ]),
            ],
            'auth' => [
                'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'surname', 'email', 'preferred_locale']) : null,
            ],
            'blocked_dates' => $apartment->blockedDates()
                ->orderBy('start_date')
                ->get()
                ->map(fn ($blockedDate) => [
                    'start_date' => $blockedDate->start_date?->toDateString(),
                    'end_date' => $blockedDate->end_date?->toDateString(),
                ]),
            'reservations' => $apartment->reservations()
                ->whereIn('status', [
                    Reservation::STATUS_AWAITING_PAYMENT,
                    Reservation::STATUS_PENDING,
                    Reservation::STATUS_CONFIRMED,
                ])
                ->orderBy('start_date')
                ->get()
                ->map(fn ($reservation) => [
                    'start_date' => $reservation->start_date?->toDateString(),
                    'end_date' => $reservation->end_date?->toDateString(),
                ]),
            'app_url' => rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/'),
        ]);
    }

    public function robots() {
        $baseUrl = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /private-area',
            "Sitemap: {$baseUrl}/sitemap.xml",
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sitemap() {
        $baseUrl = rtrim(config('app.url') ?: request()->getSchemeAndHttpHost(), '/');
        $lastmod = now()->toDateString();
        $url = "{$baseUrl}/";

        $xml = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{$url}</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    </urlset>
    XML;

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function privateArea() {
        $user = Auth::user();

        return Inertia::render('PrivateArea', [
            'auth' => [
                'user' => $user ? $user->only(['id', 'name', 'surname', 'email', 'phone', 'preferred_locale']) : null,
            ],
            'reservations' => $user
                ? $user->reservations()
                    ->with(['apartment', 'payments'])
                    ->orderBy('start_date')
                    ->get()
                    ->map(function ($reservation) {
                        $paidSettled = round(
                            (float) $reservation->payments
                                ->where('status', Payment::STATUS_PAID)
                                ->sum(fn (Payment $payment) => (float) $payment->amount),
                            2
                        );
                        $authorizedAmount = round(
                            (float) $reservation->payments
                                ->where('status', Payment::STATUS_AUTHORIZED)
                                ->sum(fn (Payment $payment) => (float) $payment->amount),
                            2
                        );
                        $totalCovered = round($paidSettled + $authorizedAmount, 2);
                        $nextPendingPayment = $reservation->payments
                            ->where('status', Payment::STATUS_PENDING)
                            ->sortBy(fn ($payment) => match ($payment->step) {
                                Payment::STEP_DEPOSIT => 0,
                                Payment::STEP_BALANCE => 1,
                                default => 2,
                            })
                            ->first();

                        $total = (float) $reservation->total;
                        $effectiveStatus = $reservation->status;

                        if (
                            $reservation->status === Reservation::STATUS_PENDING
                            && $totalCovered <= 0
                            && $nextPendingPayment
                        ) {
                            $effectiveStatus = Reservation::STATUS_AWAITING_PAYMENT;
                        }

                        if (
                            $reservation->status === Reservation::STATUS_AWAITING_PAYMENT
                            && $totalCovered > 0
                        ) {
                            $effectiveStatus = Reservation::STATUS_PENDING;
                        }

                        return [
                            'id' => $reservation->id,
                            'status' => $effectiveStatus,
                            'start_date' => $reservation->start_date?->toDateString(),
                            'end_date' => $reservation->end_date?->toDateString(),
                            'total' => $total,
                            'total_paid' => $totalCovered,
                            'total_paid_settled' => $paidSettled,
                            'total_authorized' => $authorizedAmount,
                            'amount_due' => max(0, round($total - $totalCovered, 2)),
                            'next_pending_payment' => $nextPendingPayment ? [
                                'id' => $nextPendingPayment->id,
                                'step' => $nextPendingPayment->step,
                                'amount' => (float) $nextPendingPayment->amount,
                                'currency' => $nextPendingPayment->currency,
                                'due_at' => $nextPendingPayment->due_at?->toDateString(),
                            ] : null,
                            'apartment' => [
                                'name_it' => $reservation->apartment?->name_it,
                                'name_en' => $reservation->apartment?->name_en,
                            ],
                        ];
                    })
                    ->values()
                : [],
        ]);
    }

    public function profile(Request $request) {
        $user = $request->user();

        abort_unless($user, 403);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'preferred_locale' => ['nullable', Rule::in(LocalePreference::supportedLocales())],
        ]);

        $data['preferred_locale'] = LocalePreference::normalize(
            (string) ($data['preferred_locale'] ?? ''),
            $user->preferred_locale
        );

        $user->update($data);

        return back();
    }
}
