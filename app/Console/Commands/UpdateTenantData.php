<?php

    namespace App\Console\Commands;

    use App\Enums\DefaultPaymentMethods;
    use App\Models\PaymentMethod;
    use Illuminate\Console\Command;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    class UpdateTenantData extends Command
    {
        use TenantAwareCommand , HasATenantsOption;

        protected $signature = 'update-wallet-payment-method';

        protected $description = 'Command description';


        public function handle() : void
        {
//            Tenant::all()->runForEach( function ($tenant) {
            $p = PaymentMethod::firstWhere( 'name' , 'Wallet' );
            $p?->update( [ 'name' => DefaultPaymentMethods::WALLET->value ] );
//            } );
        }
    }
