<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\BlockedDate;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Models\UserGroup;
use App\Support\LocalePreference;
use App\Services\ReservationCancellationService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Throwable;

class WebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return to_route('private-area.index');
        }

        return Inertia::render('Login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'preferred_locale' => ['nullable', Rule::in(LocalePreference::supportedLocales())],
        ]);

        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $preferredLocale = trim((string) ($data['preferred_locale'] ?? ''));
            $user = $request->user();

            if ($user && $preferredLocale !== '') {
                $normalizedLocale = LocalePreference::normalize($preferredLocale, $user->preferred_locale);

                if ($user->preferred_locale !== $normalizedLocale) {
                    $user->forceFill(['preferred_locale' => $normalizedLocale])->save();
                }
            }

            return redirect()->intended(route('private-area.index'));
        }

        return back()->withErrors([
            'email' => 'Credenziali non valide.',
        ]);
    }

    public function bookingRequest(Request $request)
    {
        $authUser = $request->user();
        $isGuestBooking = ! $authUser;

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
            'start_date' => ['required', 'date', 'after:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'payment_plan' => ['nullable', 'in:full,split'],
            'payment_locale' => ['nullable', Rule::in(LocalePreference::supportedLocales())],
        ]);

        $apartment = Apartment::query()->findOrFail($data['apartment_id']);
        $maxGuests = max(1, (int) ($apartment->max_guests ?? 1));

        if ((int) $data['guests_count'] > $maxGuests) {
            return back()->withErrors([
                'guests_count' => "Numero massimo ospiti consentito: {$maxGuests}.",
            ]);
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $overlapExists = Reservation::query()
            ->where('apartment_id', $data['apartment_id'])
            ->whereIn('status', [
                Reservation::STATUS_AWAITING_PAYMENT,
                Reservation::STATUS_PENDING,
                Reservation::STATUS_CONFIRMED,
            ])
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
        $requestedLocale = trim((string) ($data['payment_locale'] ?? ''));
        $normalizedRequestedLocale = $requestedLocale !== ''
            ? LocalePreference::normalize($requestedLocale)
            : null;

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
                'password' => Hash::make(Str::random(32)),
                'user_group_id' => $customerGroup->id,
                'preferred_locale' => $normalizedRequestedLocale ?? LocalePreference::defaultLocale(),
            ]);
        } else {
            $updates = [];

            if ($name !== '' && $user->name === '') {
                $updates['name'] = $name;
            }

            if ($surname !== '' && $user->surname === '') {
                $updates['surname'] = $surname;
            }

            if ($normalizedRequestedLocale !== null && $user->preferred_locale !== $normalizedRequestedLocale) {
                $updates['preferred_locale'] = $normalizedRequestedLocale;
            }

            if ($updates) {
                $user->update($updates);
            }
        }

        $base = (float) ($apartment->base_price ?? 0);
        $extras = [
            (float) ($apartment->extra_guest_price_2 ?? 0),
            (float) ($apartment->extra_guest_price_3 ?? 0),
            (float) ($apartment->extra_guest_price_4 ?? 0),
        ];
        $extraCount = max(0, (int) $data['guests_count'] - 1);
        $perNight = $base + array_sum(array_slice($extras, 0, $extraCount));
        $nights = max(1, $startDate->diffInDays($endDate));
        $subtotal = $perNight * $nights;

        $reservation = Reservation::create([
            'customer_id' => $user->id,
            'apartment_id' => $apartment->id,
            'status' => Reservation::STATUS_AWAITING_PAYMENT,
            'guests_count' => $data['guests_count'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'subtotal' => $subtotal,
            'discount_percent' => 0,
            'total' => $subtotal,
            'total_paid' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        $paymentPlan = $data['payment_plan'] ?? 'full';
        $locale = LocalePreference::normalize($normalizedRequestedLocale, $user->preferred_locale);
        $currency = config('services.stripe.currency', 'eur');

        if ($paymentPlan === 'split') {
            $depositAmount = round($subtotal * 0.30, 2);
            $balanceAmount = round($subtotal - $depositAmount, 2);

            $reservation->payments()->create([
                'provider' => 'stripe',
                'step' => Payment::STEP_DEPOSIT,
                'status' => Payment::STATUS_PENDING,
                'amount' => $depositAmount,
                'currency' => $currency,
                'locale' => $locale,
            ]);

            $reservation->payments()->create([
                'provider' => 'stripe',
                'step' => Payment::STEP_BALANCE,
                'status' => Payment::STATUS_PENDING,
                'amount' => $balanceAmount,
                'currency' => $currency,
                'due_at' => $startDate->copy()->subDays(7),
                'locale' => $locale,
            ]);
        } else {
            $reservation->payments()->create([
                'provider' => 'stripe',
                'step' => Payment::STEP_FULL,
                'status' => Payment::STATUS_PENDING,
                'amount' => $subtotal,
                'currency' => $currency,
                'locale' => $locale,
            ]);
        }

        if ($isGuestBooking) {
            return back()->with('booking_notice', 'guest_registration_required');
        }

        $payment = $reservation->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->orderByRaw("case when step = 'deposit' then 0 when step = 'balance' then 1 else 2 end")
            ->first();

        try {
            $session = app(StripeCheckoutService::class)
                ->createSession($payment->load('reservation.customer'));

            $payment->forceFill([
                'stripe_checkout_session_id' => $session['id'],
            ])->save();

            return Inertia::location($session['url']);
        } catch (Throwable $exception) {
            return back()->withErrors([
                'payment_plan' => 'Pagamento non disponibile. Riprova piu tardi.',
            ]);
        }
    }

    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG || $user->id !== $reservation->customer_id) {
            abort(403, 'Accesso non autorizzato');
        }

        app(ReservationCancellationService::class)->cancelByCustomer($reservation);

        return back();
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return to_route('private-area.index');
        }

        return Inertia::render('Register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'preferred_locale' => ['nullable', Rule::in(LocalePreference::supportedLocales())],
        ]);

        $group = UserGroup::firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        $name = trim((string) ($data['name'] ?? ''));
        $surname = trim((string) ($data['surname'] ?? ''));
        $fallbackName = strtok($data['email'], '@') ?: 'Cliente';
        $preferredLocale = LocalePreference::normalize((string) ($data['preferred_locale'] ?? null));

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
                'preferred_locale' => $preferredLocale,
            ]);
        } else {
            $user = User::create([
                'name' => $name !== '' ? $name : ucfirst($fallbackName),
                'surname' => $surname !== '' ? $surname : 'Cliente',
                'email' => $data['email'],
                'password' => $data['password'],
                'user_group_id' => $group->id,
                'preferred_locale' => $preferredLocale,
            ]);
        }

        Auth::login($user);

        return to_route('private-area.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
