<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Http\Requests\TenantRequest;
    use App\Http\Resources\TenantResource;
    use App\Jobs\InitiatePaymentJob;
    use App\Models\BillingCycle;
    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use App\Models\TenantSubscription;
    use Illuminate\Support\Facades\DB;

    class TenantController extends Controller
    {
        public function index()
        {
            return TenantResource::collection( Tenant::all() );
        }

        public function show(Tenant $tenant)
        {
            return new TenantResource( $tenant );
        }

        public function store(TenantRequest $request)
        {
            try {
                return DB::transaction( function () use ($request) {
                    $data = $request->validated();

                    BusinessOnBoard::create( [
                        'address'             => $data[ 'businessAddress' ] ,
                        'admin_email'         => $data[ 'adminEmail' ] ,
                        'admin_name'          => $data[ 'adminName' ] ,
                        'admin_password'      => $data[ 'adminPassword' ] ,
                        'admin_pin'           => $data[ 'adminPin' ] ,
                        'amount'              => $data[ 'amountPaid' ] ,
                        'cycle_id'            => $data[ 'billingCycleId' ] ,
                        'email'               => $data[ 'businessEmail' ] ,
                        'mobile_phone_number' => $data[ 'mobileMoneyNumber' ] ,
                        'name'                => $data[ 'businessName' ] ,
                        'payment_method'      => $data[ 'paymentMethod' ] ,
                        'phone'               => $data[ 'businessPhone' ] ,
                        'plan_id'             => $data[ 'subscriptionPlanId' ] ,
                        'tenant'              => $data[ 'tenant' ] ,
                    ] );

                    $subscription = TenantSubscription::create( [
                        'phone'                => $data[ 'mobileMoneyNumber' ] ,
                        'amount'               => $data[ 'amountPaid' ] ,
                        'billing_cycle_id'     => $data[ 'billingCycleId' ] ,
                        'tenant_id'            => $data[ 'tenant' ] ,
                        'subscription_plan_id' => $data[ 'subscriptionPlanId' ] ,
                        'status'               => Status::INACTIVE ,
                    ] );

                    $cycle = BillingCycle::find( $data[ 'billingCycleId' ] );

                    $subscription->update( [
                        'invoice_no' => recordId( 'INV' , $subscription ) ,
                        'expires_at' => now()->addMonths( $cycle->multiplier )
                    ] );

                    InitiatePaymentJob::dispatch( $subscription );

                    return response()->json();
                } );

            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }

        }

        public function destroy(Tenant $tenant)
        {
            $tenant->delete();
        }
    }
