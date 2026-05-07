<?php

    namespace App\Jobs;

    use App\Http\Controllers\PaymentsController;
    use App\Models\TenantSubscription;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class InitiatePaymentJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public TenantSubscription $tenantSubscription) {}

        public function handle(PaymentsController $payments_controller) : void
        {
            $payments_controller->yoPay( $this->tenantSubscription );
        }
    }
