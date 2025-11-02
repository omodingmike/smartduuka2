<?php

    namespace Database\Seeders;

    use App\Models\Subscription;
    use Dipokhalder\EnvEditor\EnvEditor;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Str;

    class NewClientSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run() : void
        {
            $project_id   = Str::uuid()->getHex();
            $business_id  = time();
            $subscription = Subscription::create(
                [
                    'plan_id'               => 1 ,
                    'project_id'            => $project_id ,
                    'business_id'           => $business_id ,
                    'invoice_no'            => Str::random( 10 ) ,
                    'external_id'           => Str::random( 10 ) ,
                    'vendor_transaction_id' => Str::random( 10 ) ,
                    'vendor_message'        => 'success' ,
                    'phone'                 => '+256701234567' ,
                    'status'                => 'active' ,
                    'amount'                => 50000 ,
                    'payment_status'        => 'success' ,
                    'expires_at'            => now()->addDays( 30 ) ,
                ]
            );
            if ( $subscription ) {
                $env_editor = new EnvEditor;
                $data       = [ 'BUSINESS_ID' => $business_id , 'PROJECT_ID' => $project_id ];
                $env_editor->addData( $data );
                Artisan::call( 'mp:seed' );
            }
        }
    }
