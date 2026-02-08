<?php

use Inertia\Inertia;
use App\Models\Apartment;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
    return Inertia::render('PrivateArea', [
        'auth' => [
            'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'surname', 'email', 'phone']) : null,
        ],
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
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);

    $group = UserGroup::firstOrCreate(
        ['slug' => UserGroup::CUSTOMER_SLUG],
        ['name' => 'Customer'],
    );

    $name = trim((string) ($data['name'] ?? ''));
    $surname = trim((string) ($data['surname'] ?? ''));
    $fallbackName = strtok($data['email'], '@') ?: 'Cliente';

    $user = User::create([
        'name' => $name !== '' ? $name : ucfirst($fallbackName),
        'surname' => $surname !== '' ? $surname : 'Cliente',
        'email' => $data['email'],
        'password' => $data['password'],
        'user_group_id' => $group->id,
    ]);

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
