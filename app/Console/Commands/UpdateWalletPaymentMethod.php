<?php

    namespace App\Console\Commands;

    use App\Models\PaymentMethod;
    use App\Models\Tenant;
    use Illuminate\Console\Command;

    class UpdateWalletPaymentMethod extends Command
    {
        protected $signature = 'update-wallet-payment-method';

        protected $description = 'Command description';


        public function handle() : void
        {
            Tenant::all()->runForEach( function ($tenant) {
                PaymentMethod::firstWhere( 'name' , 'Wallet' )->update( [ 'name' => 'Wallet Deposits' ] );
            } );
        }
    }
