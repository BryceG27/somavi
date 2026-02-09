<?php

use Inertia\Inertia;
use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use App\Jobs\CheckExternalAvailability;
use App\Http\Controllers\StripeController;
use App\Services\StripeCheckoutService;
use App\Services\ReservationCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

Route::get('/', function () {
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
                'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'surname', 'email']) : null,
            ],
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
            'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'surname', 'email']) : null,
        ],
    ]);
});

Route::get('/area-privata', function () {
    $user = Auth::user();

    return Inertia::render('PrivateArea', [
        'auth' => [
            'user' => $user ? $user->only(['id', 'name', 'surname', 'email', 'phone']) : null,
        ],
        'reservations' => $user
            ? $user->reservations()
                ->with('apartment')
                ->orderBy('start_date')
                ->get()
                ->map(fn ($reservation) => [
                    'id' => $reservation->id,
                    'status' => $reservation->status,
                    'start_date' => $reservation->start_date?->toDateString(),
                    'end_date' => $reservation->end_date?->toDateString(),
                    'total' => (float) $reservation->total,
                    'total_paid' => (float) $reservation->total_paid,
                    'apartment' => [
                        'name_it' => $reservation->apartment?->name_it,
                        'name_en' => $reservation->apartment?->name_en,
                    ],
                ])
                ->values()
            : [],
    ]);
})->middleware('auth');

Route::post('/area-privata/profile', function (Request $request) {
    $user = $request->user();

    abort_unless($user, 403);

    $data = $request->validate([
        'name' => ['nullable', 'string', 'max:255'],
        'surname' => ['nullable', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
        'phone' => ['nullable', 'string', 'max:255'],
    ]);

    $user->update($data);

    return back();
})->middleware('auth');

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/area-privata');
    }

    return Inertia::render('Login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, true)) {
        $request->session()->regenerate();

        return redirect()->intended('/area-privata');
    }

    return back()->withErrors([
        'email' => 'Credenziali non valide.',
    ]);
});

Route::post('/booking-request', function (Request $request) {
    $authUser = $request->user();

    if ($authUser) {
        if ($authUser->userGroup?->slug !== UserGroup::CUSTOMER_SLUG) {
            return back()->withErrors([
                'email' => 'Accesso non autorizzato.',
            ]);
        }

        $request->merge(['email' => $authUser->email]);
    }

    $data = $request->validate([
        'apartment_id' => ['required', 'exists:apartments,id'],
        'email' => ['required', 'email'],
        'name' => ['nullable', 'string', 'max:255'],
        'surname' => ['nullable', 'string', 'max:255'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after:start_date'],
        'guests_count' => ['required', 'integer', 'min:1'],
        'notes' => ['nullable', 'string'],
        'payment_plan' => ['nullable', 'in:full,split'],
        'payment_locale' => ['nullable', 'string', 'max:5'],
    ]);

    $startDate = Carbon::parse($data['start_date']);
    $endDate = Carbon::parse($data['end_date']);

    $overlapExists = Reservation::query()
        ->where('apartment_id', $data['apartment_id'])
        ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
        ->where(function ($query) use ($startDate, $endDate) {
            $query
                ->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate);
        })
        ->exists();

    $blockedExists = BlockedDate::query()
        ->where('apartment_id', $data['apartment_id'])
        ->where('start_date', '<', $endDate)
        ->where('end_date', '>', $startDate)
        ->exists();

    if ($overlapExists || $blockedExists) {
        return back()->withErrors([
            'start_date' => 'Periodo non disponibile.',
        ]);
    }

    $customerGroup = UserGroup::query()->firstOrCreate(
        ['slug' => UserGroup::CUSTOMER_SLUG],
        ['name' => 'Customer'],
    );

    $email = strtolower(trim((string) $data['email']));
    $name = trim((string) ($data['name'] ?? ''));
    $surname = trim((string) ($data['surname'] ?? ''));

    $user = $authUser ?: User::query()->where('email', $email)->first();

    if ($user && $user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG) {
        return back()->withErrors([
            'email' => 'Accesso non autorizzato.',
        ]);
    }

    if (! $user) {
        $user = User::create([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
            'user_group_id' => $customerGroup->id,
        ]);
    } else {
        $updates = [];

        if ($name !== '' && $user->name === '') {
            $updates['name'] = $name;
        }

        if ($surname !== '' && $user->surname === '') {
            $updates['surname'] = $surname;
        }

        if ($updates) {
            $user->update($updates);
        }
    }

    $apartment = Apartment::query()->findOrFail($data['apartment_id']);

    $base = (float) ($apartment->base_price ?? 0);
    $extras = [
        (float) ($apartment->extra_guest_price_2 ?? 0),
        (float) ($apartment->extra_guest_price_3 ?? 0),
        (float) ($apartment->extra_guest_price_4 ?? 0),
    ];
    $extraCount = max(0, $data['guests_count'] - 1);
    $perNight = $base + array_sum(array_slice($extras, 0, $extraCount));
    $nights = max(1, $startDate->diffInDays($endDate));
    $subtotal = $perNight * $nights;

    $reservation = Reservation::create([
        'customer_id' => $user->id,
        'apartment_id' => $apartment->id,
        'status' => Reservation::STATUS_PENDING,
        'guests_count' => $data['guests_count'],
        'start_date' => $startDate,
        'end_date' => $endDate,
        'subtotal' => $subtotal,
        'discount_percent' => 0,
        'total' => $subtotal,
        'total_paid' => 0,
        'notes' => $data['notes'] ?? null,
    ]);

    CheckExternalAvailability::dispatch($reservation->id);

    $paymentPlan = $data['payment_plan'] ?? 'full';
    $locale = $data['payment_locale'] ?? null;
    $currency = config('services.stripe.currency', 'eur');

    if ($paymentPlan === 'split') {
        $depositAmount = round($subtotal * 0.30, 2);
        $balanceAmount = round($subtotal - $depositAmount, 2);

        $reservation->payments()->create([
            'provider' => 'stripe',
            'step' => \App\Models\Payment::STEP_DEPOSIT,
            'status' => \App\Models\Payment::STATUS_PENDING,
            'amount' => $depositAmount,
            'currency' => $currency,
            'locale' => $locale,
        ]);

        $reservation->payments()->create([
            'provider' => 'stripe',
            'step' => \App\Models\Payment::STEP_BALANCE,
            'status' => \App\Models\Payment::STATUS_PENDING,
            'amount' => $balanceAmount,
            'currency' => $currency,
            'due_at' => $startDate->copy()->subDays(7),
            'locale' => $locale,
        ]);
    } else {
        $reservation->payments()->create([
            'provider' => 'stripe',
            'step' => \App\Models\Payment::STEP_FULL,
            'status' => \App\Models\Payment::STATUS_PENDING,
            'amount' => $subtotal,
            'currency' => $currency,
            'locale' => $locale,
        ]);
    }

    $payment = $reservation->payments()
        ->where('status', \App\Models\Payment::STATUS_PENDING)
        ->orderByRaw("case when step = 'deposit' then 0 when step = 'balance' then 1 else 2 end")
        ->first();

    try {
        $session = app(StripeCheckoutService::class)
            ->createSession($payment->load('reservation.customer'));

        $payment->forceFill([
            'stripe_checkout_session_id' => $session['id'],
        ])->save();

        return Inertia::location($session['url']);
    } catch (\Throwable $exception) {
        return back()->withErrors([
            'payment_plan' => 'Pagamento non disponibile. Riprova piu tardi.',
        ]);
    }
});

Route::post('/payments/stripe/checkout/{reservation}', [StripeController::class, 'checkout']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/reservations/{reservation}/cancel', function (Request $request, Reservation $reservation) {
    $user = $request->user();

    abort_unless($user, 403);

    if ($user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG || $user->id !== $reservation->customer_id) {
        abort(403, 'Accesso non autorizzato');
    }

    app(ReservationCancellationService::class)->cancelByCustomer($reservation);

    return back();
})->middleware('auth');

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect('/area-privata');
    }

    return Inertia::render('Register');
});

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['nullable', 'string', 'max:255'],
        'surname' => ['nullable', 'string', 'max:255'],
        'email' => ['required', 'email'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);

    $group = UserGroup::firstOrCreate(
        ['slug' => UserGroup::CUSTOMER_SLUG],
        ['name' => 'Customer'],
    );

    $name = trim((string) ($data['name'] ?? ''));
    $surname = trim((string) ($data['surname'] ?? ''));
    $fallbackName = strtok($data['email'], '@') ?: 'Cliente';

    $user = User::query()->where('email', $data['email'])->first();

    if ($user && $user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG) {
        return back()->withErrors([
            'email' => 'Accesso non autorizzato.',
        ]);
    }

    if ($user) {
        $user->update([
            'name' => $name !== '' ? $name : $user->name,
            'surname' => $surname !== '' ? $surname : $user->surname,
            'password' => $data['password'],
            'user_group_id' => $group->id,
        ]);
    } else {
        $user = User::create([
            'name' => $name !== '' ? $name : ucfirst($fallbackName),
            'surname' => $surname !== '' ? $surname : 'Cliente',
            'email' => $data['email'],
            'password' => $data['password'],
            'user_group_id' => $group->id,
        ]);
    }

    Auth::login($user);

    return redirect('/area-privata');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
});

Route::get('/fresh-deploy', function() {
    Artisan::call('migrate:refresh --step --seed');
});

Route::get('/storage-link', function() {
    Artisan::call('storage:link');
});

Route::get('/get-routes', function() {
    Artisan::call('route:list');

    dd(Artisan::output());
});

Route::get('/patch', function() {
    Artisan::call('vendor:publish --tag=livewire:assets --force');
    Artisan::call('optimize:clear');
});
