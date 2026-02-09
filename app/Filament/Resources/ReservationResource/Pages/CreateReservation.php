<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use App\Filament\Resources\ReservationResource;
use App\Models\User;
use App\Models\UserGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $email = strtolower(trim((string) ($data['customer_email'] ?? '')));

        $customerGroup = UserGroup::query()
            ->firstOrCreate(
                ['slug' => UserGroup::CUSTOMER_SLUG],
                ['name' => 'Customer'],
            );

        $user = User::query()->where('email', $email)->first();

        if ($user && $user->userGroup?->slug !== UserGroup::CUSTOMER_SLUG) {
            throw ValidationException::withMessages([
                'customer_email' => 'Email gia associata a un account non cliente.',
            ]);
        }

        if (! $user) {
            $user = User::create([
                'name' => '',
                'surname' => '',
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'user_group_id' => $customerGroup->id,
            ]);
        }

        $data['customer_id'] = $user->id;
        unset($data['customer_email']);

        return $data;
    }
}
