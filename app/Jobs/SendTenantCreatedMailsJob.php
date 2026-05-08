<?php

    namespace App\Jobs;

    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class SendTenantCreatedMailsJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public Tenant $tenant) {}

        public function handle() : void
        {
            $tenant  = $this->tenant->getTenantKey();
            $onboard = BusinessOnBoard::where( 'tenant' , $tenant )->first();
            $data    = [
                'username'       => $onboard->admin_name ,
                'business_name'  => $onboard->name ,
                'dashboard_link' => $onboard->domain ,
            ];
            SendEmailsJob::dispatch( $onboard->admin_email ,
                'Welcome to the Smart Duuka' ,
                'tenants.WelcomeToSmartduukaTemplate' ,
                $data );
        }
    }
