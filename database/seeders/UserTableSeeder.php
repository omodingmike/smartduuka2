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
            $targetUsernames = [ 'admin' , 'default-customer' ];

            $existingCount = User::whereIn( 'username' , $targetUsernames )->count();

            if ( $existingCount === count( $targetUsernames ) ) {
                return;
            }

            $admin = User::firstOrCreate(
                [
                    'username' => 'admin1' ,
//                    'email'    => 'support12@smartduuka.com' ,
                    'email'    => 'support@smartduuka.com' ,
                ] ,
                [
                    'email'             => 'support@smartduuka.com' ,
                    'phone'             => '256701234567' ,
                    'name'              => 'Support Admin' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'country_code'      => '+256' ,
                    'is_guest'          => Ask::NO
                ]
            );

            $adminRole = Role::findByName( EnumRole::ADMIN , 'sanctum' );
            $admin->syncRoles( [ $adminRole ] );

            $customer = User::firstOrCreate(
                [
                    'username' => 'customer'
                ] ,
                [
                    'email'             => 'walkingcustomer12@example.com' ,
                    'phone'             => '0701234567' ,
                    'type'              => 'Retail' ,
                    'name'              => 'Walking Customer' ,
                    'email_verified_at' => now() ,
                    'password'          => bcrypt( 'Admin@support12' ) ,
                    'status'            => Status::ACTIVE ,
                    'is_guest'          => Ask::NO
                ]
            );

            $customerRole = Role::findByName( EnumRole::CUSTOMER , 'sanctum' );
            $customer->syncRoles( [ $customerRole ] );
        }
    }