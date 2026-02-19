<?php

    namespace Database\Seeders;

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Enums\Status;
    use App\Models\User;
    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Role;


    class UserTableSeeder extends Seeder
    {

        public function run() : void
        {
            $admin = User::updateOrCreate(
                [
                    'email'    => 'support@smartduuka.com' ,
                    'phone'    => '0701034242' ,
                    'username' => 'admin'
                ] ,
                [
                    'name'              => 'John Doe' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'country_code'      => '+880' ,
                    'is_guest'          => Ask::NO
                ]
            );
            $admin->assignRole( Role::find( EnumRole::ADMIN ) );

            $customer = User::updateOrCreate(
                [
                    'email'    => 'walkingcustomer@example.com' ,
                    'phone'    => '0701234567' ,
                    'username' => 'default-customer'
                ] ,
                [
                    'name'              => 'Walking Customer' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'is_guest'          => Ask::NO
                ]
            );
            $customer->assignRole( Role::find( EnumRole::CUSTOMER ) );
        }
    }
