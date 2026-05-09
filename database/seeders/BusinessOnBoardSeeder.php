<?php

    namespace Database\Seeders;

    use App\Enums\Role;
    use App\Enums\Status;
    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use App\Models\User;
    use Illuminate\Database\Seeder;
    use Smartisan\Settings\Facades\Settings;

    class BusinessOnBoardSeeder extends Seeder
    {
        public function run() : void
        {
            $records = [];

            Tenant::all()->runForEach( function (Tenant $tenant) use (&$records) {

                tenancy()->initialize( $tenant->id );
                $company = Settings::group( 'company' )->all();

                $admin = User::role( Role::ADMIN )
                             ->when( app()->isProduction() , fn($q) => $q
                                 ->whereNotIn( 'email' , [ 'support12@smartduuka.com' , 'support@smartduuka.com' ] )
                             )
                             ->where( 'status' , Status::ACTIVE )
                             ->first();

                if ( ! $admin ) {
                    $this->command->warn( "Tenant [{$tenant->id}]: no active admin found, skipping." );
                    return;
                }

                $records[] = [
                    'address'             => data_get( $company , 'company_address' , '' ) ,
                    'admin_email'         => $admin->email ,
                    'admin_name'          => $admin->name ,
                    'admin_password'      => 'password' ,
                    'admin_pin'           => 'pin' ,
                    'amount'              => 1000 ,
                    'status'              => Status::ACTIVE ,
                    'cycle_id'            => 1 ,
                    'email'               => data_get( $company , 'company_email' , '' ) ,
                    'mobile_phone_number' => $admin->phone ,
                    'name'                => data_get( $company , 'company_name' , '' ) ,
                    'payment_method'      => 'mm' ,
                    'phone'               => data_get( $company , 'company_phone' , '' ) ,
                    'plan_id'             => 1 ,
                    'tenant'              => $tenant->id ,
                    'created_at'          => now() ,
                    'updated_at'          => now() ,
                ];
            } );

            if ( empty( $records ) ) {
                return;
            }

            // Single central DB call instead of one per tenant
            tenancy()->central( function () use ($records) {
                BusinessOnBoard::insert( $records );
            } );
            tenancy()->end();
        }
    }