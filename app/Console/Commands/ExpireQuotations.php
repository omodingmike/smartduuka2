<?php

    namespace App\Console\Commands;

    use App\Enums\QuotationStatus;
    use App\Models\Order;
    use Illuminate\Console\Command;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    class ExpireQuotations extends Command
    {
        use TenantAwareCommand , HasATenantsOption;

        protected $signature = 'quotations:expire';

        protected $description = 'Mark overdue quotation orders as expired';

        public function handle() : void
        {
//            Tenant::all()->runForEach( function (Tenant $tenant) {
            Order::query()
                 ->where( 'due_date' , '<' , now() )
                 ->where( 'quotation_status' , '<>' , QuotationStatus::EXPIRED )
                 ->chunkById( 100 , function ($orders) {
                     foreach ( $orders as $order ) {
                         $order->update( [ 'quotation_status' => QuotationStatus::EXPIRED ] );
                     }
                 } );
//            } );
        }
    }
