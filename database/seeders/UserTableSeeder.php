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
            if ( ! User::where( 'email' , 'support@smartduuka.com' )->exists() ) {
                $admin = User::create( [
                    'name'              => 'John Doe' ,
                    'email'             => 'support@smartduuka.com' ,
                    'phone'             => '0701034242' ,
                    'username'          => 'admin' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'country_code'      => '+880' ,
                    'is_guest'          => Ask::NO
                ] );
                $admin->assignRole( Role::find( EnumRole::ADMIN ) );
            }

            if ( ! User::where( 'email' , 'walkingcustomer@example.com' )->exists() ) {
                $customer = User::create( [
                    'name'              => 'Walking Customer' ,
                    'email'             => 'walkingcustomer@example.com' ,
                    'phone'             => '0701234567' ,
                    'username'          => 'default-customer' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'is_guest'          => Ask::NO
                ] );
                $customer->assignRole( Role::find( EnumRole::CUSTOMER ) );
            }
        }
    }
