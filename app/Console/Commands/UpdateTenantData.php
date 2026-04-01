<?php

    namespace App\Console\Commands;

    use App\Enums\DefaultPaymentMethods;
    use App\Models\PaymentMethod;
    use App\Models\Tenant;
    use Illuminate\Console\Command;

    class UpdateTenantData extends Command
    {
        protected $signature = 'update-wallet-payment-method';

        protected $description = 'Command description';


        public function handle() : void
        {
            Tenant::all()->runForEach( function ($tenant) {
                $p = PaymentMethod::firstWhere( 'name' , 'Wallet' );
                $p?->update( [ 'name' => DefaultPaymentMethods::WALLET->value ] );
            } );
        }
    }
