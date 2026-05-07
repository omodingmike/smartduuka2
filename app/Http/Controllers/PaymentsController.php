<?php

    namespace App\Http\Controllers;

    use App\Enums\SubscriptionPaymentStatus;
    use App\Models\TenantSubscription;
    use App\YoPayments\YoAPI;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Str;

    class PaymentsController extends Controller
    {
        public function yoUganda(Request $request)
        {
            $username          = config( 'payments.yo.username' );
            $password          = config( 'payments.yo.password' );
            $mode              = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI             = new YoAPI( username: $username , password: $password , mode: $mode );
            $is_from_yo_uganda = $yoAPI->receive_payment_notification( $request );

            if ( $is_from_yo_uganda ) {
                $subscription = TenantSubscription::where( [ 'transaction_id' => $request->external_ref ] )->first();
                if ( $subscription ) {
                    $subscription->update( [
                        'payment_status' => SubscriptionPaymentStatus::Paid ,
                    ] );
                    Cache::forget( "tenant_subscription_{$subscription->tenant_id}" );
                }
            }
            return response()->json();
        }

        public function yoPay(TenantSubscription $tenantSubscription)
        {
            $username = config( 'payments.yo.username' );
            $password = config( 'payments.yo.password' );

            $transaction_id = Str::uuid()->getHex();
            $phone          = $tenantSubscription->phone;
            $phone          = '256' . substr( $phone , 1 );

            $mode   = app()->isLocal() ? 'sandbox' : 'production';
            $yoAPI  = new YoAPI( username: $username , password: $password , mode: $mode );
            $amount = $tenantSubscription->amount;

            $title = 'Smart Duuka Subscription';

            $yoAPI->set_external_reference( $transaction_id );
            $yoAPI->set_nonblocking( 'TRUE' );

            if ( app()->isLocal() ) {
                $ipn = 'https://ztmmx82nsn.sharedwithexpose.com/api/webhook/yo';
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
    }
