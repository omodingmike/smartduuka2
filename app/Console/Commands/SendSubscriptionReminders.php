<?php

    namespace App\Console\Commands;

    use App\Jobs\SendEmailsJob;
    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use App\Notifications\SubscriptionExpiring;
    use Illuminate\Console\Command;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Notification;
    use Smartisan\Settings\Settings;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    class SendSubscriptionReminders extends Command
    {
        use TenantAwareCommand , HasATenantsOption;
        protected $signature = 'subscriptions:send-reminders';

        protected $description = 'Send email and WhatsApp reminders for expiring and expired subscriptions';

        private const array EXPIRED_REMINDER_DAYS = [ 3 , 7 , 14 ];
        private const int   DELETION_WARNING_DAY  = 25;
        private const int   TOTAL_RETENTION_DAYS  = 30;

        public function handle() : void
        {
//            Tenant::all()->runForEach( function (Tenant $tenant) {
                tenancy()->central( function () use ($tenant) {
                    $this->processReminder( $tenant );
                } );
//            } );

            $this->info( 'Subscription reminder process completed.' );
        }

        private function processReminder(Tenant $tenant) : void
        {
            $tenantId     = $tenant->id;
            $subscription = tenantSubscriptions( $tenantId )->first();

            if ( ! $subscription ) {
                return;
            }

            $onboard = BusinessOnBoard::where( 'tenant' , $tenantId )->latest()->first();

            if ( ! $onboard ) {
                $this->warn( "No onboard record found for tenant: {$tenantId}" );
                return;
            }

            $expiresAt = Carbon::parse( $subscription->expires_at );
            $now       = now();

            $template            = NULL;
            $subject             = NULL;
            $notificationTitle   = NULL;
            $notificationMessage = NULL;

            $session_domain = config( 'session.domain' );

            $emailData = [
                'username'      => $onboard->admin_name ,
                'business_name' => $onboard->name ,
                'business_id'   => $tenant->business_id ,
                'expiry_date'   => $expiresAt->format( 'd-m-y H:i:s' ) ,
                'login_link'    => $onboard->domain ?? "https://{$tenantId}$session_domain" ,
            ];

            if ( $expiresAt->isFuture() ) {
                $daysLeft  = (int) $now->diffInDays( $expiresAt );
                $hoursLeft = (int) $now->diffInHours( $expiresAt );

                if ( $hoursLeft <= 24 && $hoursLeft > 0 ) {
                    $subject             = 'Urgent: 24 Hours Until Your Smart Duuka Subscription Expires';
                    $template            = 'tenants.24hrwarningtemplate';
                    $notificationTitle   = 'Subscription Expiring in 24 Hours';
                    $notificationMessage = "Your subscription for {$onboard->name} expires in less than 24 hours. Please renew to avoid service interruption.";

                }
                elseif ( $daysLeft === 7 ) {
                    $subject             = 'Subscription Reminder: 7 Days Left';
                    $template            = 'tenants.7_Days_reminder';
                    $notificationTitle   = 'Subscription Expiring in 7 Days';
                    $notificationMessage = "Your subscription for {$onboard->name} will expire in 7 days on {$expiresAt->format('d M Y')}. Renew now to avoid interruption.";
                }

            }
            else {
                $daysPast = (int) $expiresAt->diffInDays( $now );

                if ( $expiresAt->isToday() ) {
                    $subject             = 'Action Required: Your Smart Duuka Subscription Expired Today';
                    $template            = 'tenants.expiredtoday';
                    $notificationTitle   = 'Subscription Expired Today';
                    $notificationMessage = "The subscription for {$onboard->name} has expired today. Renew immediately to restore access.";

                }
                elseif ( in_array( $daysPast , self::EXPIRED_REMINDER_DAYS ) ) {
                    $subject                  = 'Restore Your Smart Duuka Access';
                    $template                 = 'tenants.expireddaysago';
                    $emailData[ 'days_past' ] = $daysPast;
                    $notificationTitle        = "Subscription Expired {$daysPast} Days Ago";
                    $notificationMessage      = "The subscription for {$onboard->name} expired {$daysPast} days ago. Renew now to restore full access.";

                }
                elseif ( $daysPast === self::DELETION_WARNING_DAY ) {
                    $daysUntilDeletion                  = self::TOTAL_RETENTION_DAYS - $daysPast;
                    $subject                            = 'Final Notice: Account Data Scheduled for Deletion';
                    $template                           = 'tenants.deletionwarning';
                    $emailData[ 'inactive_days' ]       = $daysPast;
                    $emailData[ 'days_until_deletion' ] = $daysUntilDeletion;
                    $notificationTitle                  = '⚠️ Data Deletion Warning';
                    $notificationMessage                = "Account data for {$onboard->name} will be permanently deleted in {$daysUntilDeletion} days. Renew your subscription immediately to prevent data loss.";
                }
            }

            // Send email via job
            if ( $template && $subject ) {
                SendEmailsJob::dispatch(
                    $onboard->admin_email ,
                    $subject ,
                    $template ,
                    $emailData
                );
                $this->info( "Email ({$template}) queued for tenant: {$tenantId}" );
            }

            if ( $notificationTitle && $notificationMessage ) {
                $notificationSettings = Settings::group( 'notification' )->all();

                $adminEmail = $notificationSettings[ 'admin_email' ] ?? NULL;
                $adminPhone = $notificationSettings[ 'admin_phone' ] ?? NULL;
                Notification::route( 'mail' , $adminEmail )
                            ->route( 'sms' , $adminPhone )
                            ->route( 'whatsapp' , $adminPhone )
                            ->notify( new SubscriptionExpiring( $notificationTitle , $notificationMessage ) );

                $this->info( "SubscriptionExpiring notification dispatched for tenant: {$tenantId}" );
            }
        }
    }