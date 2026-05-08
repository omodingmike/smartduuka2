<?php

    namespace App\Console\Commands;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Http\Controllers\WhatsAppController;
    use App\Jobs\SendEmailsJob;
    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use App\Models\TenantSubscription;
    use Illuminate\Console\Command;
    use Illuminate\Http\Request;
    use Illuminate\Support\Carbon;

    class SendSubscriptionReminders extends Command
    {
        protected $signature = 'subscriptions:send-reminders';

        protected $description = 'Send email and WhatsApp reminders for expiring and expired subscriptions';

        // Define when the "Expired Days Ago" and "Deletion Warning" should trigger
        private const EXPIRED_REMINDER_DAYS = [ 3 , 7 , 14 ]; // Sends 'expireddaysago' 3, 7, and 14 days after expiry
        private const DELETION_WARNING_DAY  = 25;             // Sends 'deletionwarning' 25 days after expiry
        private const TOTAL_RETENTION_DAYS  = 30;             // Data deleted after 30 days (used for the countdown)

        public function handle() : void
        {
            Tenant::all()->runForEach( function (Tenant $tenant) {
                tenancy()->central( function () use ($tenant) {
                    $this->processReminder( $tenant );
                } );
            } );

            $this->info( 'Subscription reminder process completed.' );
        }

        private function processReminder(Tenant $tenant) : void
        {
            $tenantId     = $tenant->id;
            $subscription = TenantSubscription::query()
                                              ->where( 'tenant_id' , $tenantId )
                                              ->where( 'payment_status' , SubscriptionPaymentStatus::Paid )
                                              ->where( 'status' , Status::ACTIVE )
                                              ->latest( 'expires_at' )
                                              ->first();

            if ( ! $subscription ) {
                return;
            }

            $onboard = BusinessOnBoard::where( 'tenant' , $tenantId )->first();

            if ( ! $onboard ) {
                $this->warn( "No onboard record found for tenant: {$tenantId}" );
                return;
            }

            $expiresAt = Carbon::parse( $subscription->expires_at );
            $now       = now();

            $template  = NULL;
            $subject   = NULL;
            $emailData = [
                'username'      => $onboard->admin_name ,
                'business_name' => $onboard->name ,
                'business_id'   => $tenant->business_id ,
                'expiry_date'   => $expiresAt->format( 'd-m-y H:i:s' ) ,
                'login_link'    => $onboard->domain ?? "https://{$tenantId}.smartduuka.com" ,
            ];

            if ( $expiresAt->isFuture() ) {
                $daysLeft  = (int) $now->diffInDays( $expiresAt );
                $hoursLeft = (int) $now->diffInHours( $expiresAt );

                if ( $hoursLeft <= 24 && $hoursLeft > 0 ) {
                    $subject  = 'Urgent: 24 Hours Until Your Smart Duuka Subscription Expires';
                    $template = 'tenants.24hrwarningtemplate';
                }
                elseif ( $daysLeft === 7 ) {
                    $subject  = 'Subscription Reminder: 7 Days Left';
                    $template = 'tenants.7_Days_reminder';
                }

            }
            else {

                $daysPast = (int) $expiresAt->diffInDays( $now );

                if ( $expiresAt->isToday() ) {
                    $subject  = 'Action Required: Your Smart Duuka Subscription Expired Today';
                    $template = 'tenants.expiredtoday';

                }
                elseif ( in_array( $daysPast , self::EXPIRED_REMINDER_DAYS ) ) {
                    $subject                  = 'Restore Your Smart Duuka Access';
                    $template                 = 'tenants.expireddaysago';
                    $emailData[ 'days_past' ] = $daysPast;

                }
                elseif ( $daysPast === self::DELETION_WARNING_DAY ) {
                    $subject                            = 'Final Notice: Account Data Scheduled for Deletion';
                    $template                           = 'tenants.deletionwarning';
                    $emailData[ 'inactive_days' ]       = $daysPast;
                    $emailData[ 'days_until_deletion' ] = self::TOTAL_RETENTION_DAYS - $daysPast;
                }
            }


            if ( $template && $subject ) {
                SendEmailsJob::dispatch(
                    $onboard->admin_email ,
                    $subject ,
                    $template ,
                    $emailData
                );
                $this->info( "Email ({$template}) queued for tenant: {$tenantId}" );
            }

            if ( $template && $expiresAt->diffInDays( $now ) <= 0 ) {
                $this->sendWhatsAppReminder( $onboard , $expiresAt );
            }
        }

        private function sendWhatsAppReminder($onboard , $expiresAt) : void
        {
            $request = ( new Request() )->merge( [
                'to'         => $onboard->mobile_phone_number ,
                'template'   => 'sub_reminder' ,
                'parameters' => [
                    [
                        'type'           => 'text' ,
                        'parameter_name' => 'name' ,
                        'text'           => $onboard->name ,
                    ] ,
                    [
                        'type'           => 'text' ,
                        'parameter_name' => 'date' ,
                        'text'           => $expiresAt->format( 'd-M-y H:i:s' ) ,
                    ] ,
                ] ,
            ] );

            app( WhatsAppController::class )->sendTemplateMessage( $request );
        }
    }