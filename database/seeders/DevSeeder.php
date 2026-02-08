<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminGroup = UserGroup::firstOrCreate(
            ['slug' => UserGroup::ADMIN_SLUG],
            ['name' => 'Admin'],
        );

        $customerGroup = UserGroup::firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test',
                'surname' => 'User',
                'password' => Hash::make('password'),
                'user_group_id' => $adminGroup->id,
            ],
        );

        User::firstOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'name' => 'Test',
                'surname' => 'Cliente',
                'password' => Hash::make('password'),
                'user_group_id' => $customerGroup->id,
            ],
        );
    }
}
