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
                    'username' => 'admin'
                ] ,
                [
                    'email'             => 'support@smartduuka.com' ,
                    'phone'             => '0701034242' ,
                    'name'              => 'John Doe' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'country_code'      => '+880' ,
                    'is_guest'          => Ask::NO
                ]
            );
            // Since EnumRole::ADMIN is now a string name, we can pass it directly or find by name and guard
            $adminRole = Role::findByName( EnumRole::ADMIN, 'sanctum' );
            $admin->assignRole( $adminRole );

            $customer     = User::updateOrCreate(
                [
                    'username' => 'default-customer'
                ] ,
                [
                    'email'             => 'walkingcustomer@example.com' ,
                    'phone'             => '0701234567' ,
                    'name'              => 'Walking Customer' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'is_guest'          => Ask::NO
                ]
            );

            $customerRole = Role::findByName( EnumRole::CUSTOMER, 'sanctum' );
            $customer->assignRole( $customerRole );
        }
    }
