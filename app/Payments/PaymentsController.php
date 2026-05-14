<?php

    namespace App\Payments;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use App\Enums\SubscriptionPlanType;
    use App\Http\Controllers\Controller;
    use App\Jobs\SendEmailsJob;
    use App\Models\BusinessOnBoard;
    use App\Models\SubscriptionPlan;
    use App\Models\Tenant;
    use App\Models\TenantSubscription;
    use App\Payments\DTOs\PaymentRequest;
    use App\Payments\DTOs\WebhookPayload;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;

    class PaymentsController extends Controller
    {
        public function __construct(private readonly PaymentManager $payments) {}

        public function charge(TenantSubscription $tenantSubscription) : void
        {
            $gatewayName = config( 'payments.default' , 'yo_uganda' );
            $gateway     = $this->payments->gateway( $gatewayName );

            $transactionId  = Str::uuid()->getHex();
            $paymentRequest = new PaymentRequest(
                phone: $tenantSubscription->phone ,
                amount: isDev() ? 1000 : $tenantSubscription->amount ,
                description: 'Smart Duuka Payments' ,
                transactionId: $transactionId ,
                notificationUrl: $this->webhookUrl( $gatewayName ) ,
                failureUrl: $this->webhookUrl( $gatewayName ) ,
            );

            $result = $gateway->charge( $paymentRequest );

            $tenantSubscription->update( [ 'transaction_id' => $transactionId ] );

            info( $result->message ?: 'Failed to initiate payment' );
        }

        public function webhook(Request $request , string $gateway) : JsonResponse
        {
            try {
                $handler = $this->payments->gateway( $gateway );
                $payload = $handler->parseWebhook( $request );

                if ( $handler->isSuccessWebhook( $request ) ) {
                    return $this->handleSuccess( $payload );
                }
                elseif ( $handler->isFailureWebhook( $request ) ) {
                    return $this->handleFailure( $payload );
                }
            } catch ( \Exception $e ) {
                info( $e->getMessage() );
                return response()->json();
            }

            return response()->json();
        }

        private function handleSuccess(WebhookPayload $payload)
        {
            try {
                return DB::transaction( function () use ($payload) {
                    $subscription = TenantSubscription::where( 'transaction_id' , $payload->transactionId )->first();

                    if ( ! $subscription ) {
                        return response()->json();
                    }

                    $onboard = BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )->latest()->first();
                    $onboard->update( [ 'status' => Status::ACTIVE ] );

                    $subscription->update( [
                        'payment_status' => SubscriptionPaymentStatus::Paid ,
                        'status'         => Status::ACTIVE ,
                        'transaction_id' => $payload->gatewayRef ,
                        'payer_name'     => $payload->payerName ,
                    ] );

                    // Deactivate any other active subscriptions for this tenant
                    TenantSubscription::where( 'tenant_id' , $subscription->tenant_id )
                                      ->where( 'id' , '!=' , $subscription->id )
                                      ->where( 'status' , Status::ACTIVE )
                                      ->update( [ 'status' => Status::INACTIVE ] );

                    SendEmailsJob::dispatch(
                        $onboard->admin_email ,
                        'Payment Successful - Smart Duuka' ,
                        'tenants.paymentsuccess' ,
                        [
                            'username'        => $payload->raw[ 'payer_names' ] ?? '' ,
                            'business_name'   => $onboard->name ,
                            'dashboard_link'  => $onboard->domain ,
                            'amount_paid'     => number_format( $subscription->amount ) ,
                            'txn_id'          => $payload->gatewayRef ,
                            'new_expiry_date' => $subscription->expires_at ,
                            'payment_method'  => 'Mobile Money' ,
                        ] ,
                    );

                    $plan = SubscriptionPlan::find( $subscription->subscription_plan_id );

                    if ( $plan?->type === SubscriptionPlanType::Starter ) {
                        Artisan::call( 'create-tenant' , [ 'id' => $subscription->tenant_id ] );
                    }
                    return response()->json();
                } );
            } catch ( \Throwable $e ) {
                info( $e->getMessage() );
                return response()->json();
            }
        }

        private function handleFailure(WebhookPayload $payload)
        {
            try {
                return DB::transaction( function () use ($payload) {
                    $subscription = TenantSubscription::where( 'transaction_id' , $payload->transactionId )->first();

                    if ( ! $subscription ) {
                        return response()->json();
                    }

                    $subscription->update( [ 'payment_status' => SubscriptionPaymentStatus::Failed ] );

                    $onboard    = BusinessOnBoard::where( 'tenant' , $subscription->tenant_id )->latest()->first();
                    $tenant_url = Tenant::find( $subscription->tenant_id )?->frontend_url;
                    $plan       = SubscriptionPlan::find( $subscription->subscription_plan_id );

                    $retryLink = $plan?->type === SubscriptionPlanType::Starter
                        ? 'https://smartduuka.com/pricing'
                        : "$tenant_url/subscriptions";

                    SendEmailsJob::dispatch(
                        $onboard->admin_email ,
                        'Payment Failed - Smart Duuka' ,
                        'tenants.paymentfailed' ,
                        [
                            'username'           => $payload->raw[ 'payer_names' ] ?? '' ,
                            'business_name'      => $onboard->name ,
                            'dashboard_link'     => $onboard->domain ,
                            'amount_paid'        => number_format( $subscription->amount ) ,
                            'retry_payment_link' => $retryLink ,
                        ] ,
                    );
                    return response()->json();
                } );
            } catch ( \Throwable $e ) {
                info( $e->getMessage() );
                return response()->json();
            }
        }

        private function webhookUrl(string $gateway) : string
        {
            if ( app()->isLocal() ) {
                $url = rtrim( config( 'payments.local_tunnel_url' , '' ) , '/' ) . "/api/webhook/{$gateway}";
                return $url;
            }

            return route( 'webhook.gateway' , [ 'gateway' => $gateway ] );
        }
    }
