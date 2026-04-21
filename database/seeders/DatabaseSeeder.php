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

        $user_json = json_decode(file_get_contents(database_path('/seeders/users.json')), true);

        foreach ($user_json as $user_data) {
            $user_data['password'] = Hash::make($user_data['password']);
            $user_data['user_group_id'] = $adminGroup->id;

            User::create($user_data);
        }

        $this->call(ApartmentSeeder::class);
    }
}
