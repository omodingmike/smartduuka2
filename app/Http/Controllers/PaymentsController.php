<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Enums\SubscriptionPlanType;
    use App\Jobs\SendEmailsJob;
    use App\Models\BusinessOnBoard;
    use App\Models\SubscriptionPlan;
    use App\Models\Tenant;
    use App\Models\TenantSubscription;
    use App\YoPayments\YoAPI;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    class PaymentsController extends Controller
    {
        public function yoUganda(Request $request)
        {
            $username                             = config( 'payments.yo.username' );
            $password                             = config( 'payments.yo.password' );
            $mode                                 = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI                                = new YoAPI( username: $username , password: $password , mode: $mode );
            $receive_payment_notification         = $yoAPI->receive_payment_notification( $request );
            $receive_payment_failure_notification = $yoAPI->receive_payment_failure_notification( $request );

            try {
                if ( $receive_payment_notification ) {
                    $subscription = TenantSubscription::where( [ 'transaction_id' => $request->external_ref ] )->first();

                    if ( $subscription ) {

                        DB::transaction( function () use ($subscription , $request) {
                            $onboard = BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )->latest()->first();
                            $onboard->update( [ 'status' => Status::ACTIVE ] );

                            $subscription->update( [
                                'payment_status' => SubscriptionPaymentStatus::Paid ,
                                'status'         => Status::ACTIVE ,
                                'transaction_id' => $request->network_ref ,
                            ] );
                            TenantSubscription::where( 'tenant_id' , $subscription->tenant_id )
                                              ->where( 'id' , '!=' , $subscription->id )
                                              ->where( 'status' , Status::ACTIVE )
                                              ->update( [ 'status' => Status::INACTIVE ] );
                            $data = [
                                'username'        => $request->payer_names ,
                                'business_name'   => $onboard->name ,
                                'dashboard_link'  => $onboard->domain ,
                                'amount_paid'     => number_format( $request->amount ) ,
                                'txn_id'          => $request->network_ref ,
                                'new_expiry_date' => $subscription->expires_at ,
                                'payment_method'  => 'Mobile Money' ,
                            ];

                            SendEmailsJob::dispatch( $onboard->admin_email ,
                                'Payment Successful - Smart Duuka' ,
                                'tenants.paymentsuccess' ,
                                $data );

                            $plan = SubscriptionPlan::find( $subscription->subscription_plan_id );

                            if ( $plan->type == SubscriptionPlanType::Starter ) {
                                Artisan::call( 'create-tenant' , [
                                    'id' => $subscription->tenant_id ,
                                ] );
                            }
                        } );
                    }
                }

                if ( $receive_payment_failure_notification ) {
                    DB::transaction( function () use ($receive_payment_failure_notification , $request) {
                        $subscription = TenantSubscription::where( [ 'transaction_id' => $request->failed_transaction_reference ] )->first();

                        if ( $subscription ) {
                            $subscription->update( [
                                'payment_status' => SubscriptionPaymentStatus::Failed ,
                            ] );

                            $onboard    = BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )->latest()->first();
                            $tenant_url = Tenant::find( $subscription->tenant_id )?->frontend_url;
                            $plan       = SubscriptionPlan::find( $subscription->subscription_plan_id );

                            $data = [
                                'username'       => $request->payer_names ,
                                'business_name'  => $onboard->name ,
                                'dashboard_link' => $onboard->domain ,
                                'amount_paid'    => $onboard->amount ,
                            ];
                            if ( $plan->type == SubscriptionPlanType::Starter ) {
                                $data[ 'retry_payment_link' ] = 'https://smartduuka.com/pricing';
                            }
                            else {
                                $data[ 'retry_payment_link' ] = "$tenant_url/subscriptions";
                            }

                            SendEmailsJob::dispatch( $onboard->admin_email ,
                                'Payment Failed - Smart Duuka' ,
                                'tenants.paymentfailed' ,
                                $data );
                        }
                    } );
                }
                return response()->json();
            } catch ( \Exception $e ) {
                info( $e->getMessage() );
                return response()->json();
            }
        }

        public function yoPay(TenantSubscription $tenantSubscription)
        {
            $username = config( 'payments.yo.username' );
            $password = config( 'payments.yo.password' );

            $transaction_id = Str::uuid()->getHex();
            $phone          = $tenantSubscription->phone;

            if ( str_starts_with( $phone , '+256' ) ) {
                $phone = substr( $phone , 1 );
            }
            else if ( str_starts_with( $phone , '0' ) ) {
                $phone = '256' . substr( $phone , 1 );
            }

            $mode   = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI  = new YoAPI( username: $username , password: $password , mode: $mode );
            $amount = $tenantSubscription->amount;
//            $amount = 2944; // Testing unsuccessful

            $title = 'Smart Duuka Subscription';

            $yoAPI->set_external_reference( $transaction_id );
            $yoAPI->set_nonblocking( 'TRUE' );

            if ( app()->isLocal() ) {
                $ipn = 'https://zoning-commissioners-similar-technology.trycloudflare.com/api/webhook/yo';
            }

            else {
                $ipn = route( 'webhook.yo' );
            }

            $yoAPI->set_instant_notification_url( $ipn );
            $yoAPI->set_failure_notification_url( route( 'webhook.yo' ) );
            $response = $yoAPI->ac_deposit_funds( $phone , $amount , $title );

            if ( $response[ 'Status' ] == 'OK' ) {
                $tenantSubscription->update( [ 'transaction_id' => $transaction_id ] );
            }
        }

        public function iotec(Request $request) {}
    }
