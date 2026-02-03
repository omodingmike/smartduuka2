<?php

namespace Database\Seeders;

use App\Enums\Ask;
use App\Models\User;
use App\Enums\Status;
use App\Models\Address;
use App\Enums\Role as EnumRole;
use Illuminate\Database\Seeder;
use Dipokhalder\EnvEditor\EnvEditor;
use Spatie\Permission\Models\Role;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $admin      = User::create([
            'name'              => 'John Doe',
            'email'             => 'admin@example.com',
            'phone'             => '1254875855',
            'username'          => 'admin',
            'email_verified_at' => now(),
            'password'          => bcrypt('123456'),
            'status'            => Status::ACTIVE,
            'country_code'      => '+880',
            'is_guest'          => Ask::NO
        ]);
        $admin->assignRole(Role::find(EnumRole::ADMIN));

        $customer = User::create([
            'name'              => 'Walking Customer',
            'email'             => 'walkingcustomer@example.com',
            'phone'             => '125444455',
            'username'          => 'default-customer',
            'email_verified_at' => now(),
            'password'          => bcrypt('123456'),
            'status'            => Status::ACTIVE,
            'is_guest'          => Ask::NO
        ]);
        $customer->assignRole(Role::find(EnumRole::CUSTOMER));
    }
}
