<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $adminGroup = UserGroup::firstOrCreate(
            ['slug' => UserGroup::ADMIN_SLUG],
            ['name' => 'Admin'],
        );

        UserGroup::firstOrCreate(
            ['slug' => UserGroup::CUSTOMER_SLUG],
            ['name' => 'Customer'],
        );

        User::create([
            'name' => 'Gabriele',
            'surname' => 'Fuschi',
            'email' => 'fuschigabriele88@gmail.com',
            'password' => Hash::make("Gabriele88!"),
            'user_group_id' => $adminGroup->id,
        ]);

        User::create([
            'name' => 'Fabrizio',
            'surname' => 'Guarino',
            'email' => 'fabrizio1.guarino@outlook.it',
            'password' => Hash::make("Fabr5z5698!"),
            'user_group_id' => $adminGroup->id,
        ]);

        $this->call(ApartmentSeeder::class);
    }
}
