<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\TenantSubscriptionRequest;
    use App\Http\Resources\TenantSubscriptionResource;
    use App\Models\TenantSubscription;

    class TenantSubscriptionController extends Controller
    {
        public function index()
        {
            return TenantSubscriptionResource::collection( TenantSubscription::all() );
        }

        public function store(TenantSubscriptionRequest $request)
        {
            return new TenantSubscriptionResource( TenantSubscription::create( $request->validated() ) );
        }

        public function show(TenantSubscription $tenantSubscription)
        {
            return new TenantSubscriptionResource( $tenantSubscription );
        }

        public function update(TenantSubscriptionRequest $request , TenantSubscription $tenantSubscription)
        {
            $tenantSubscription->update( $request->validated() );

            return new TenantSubscriptionResource( $tenantSubscription );
        }

        public function destroy(TenantSubscription $tenantSubscription)
        {
            $tenantSubscription->delete();

            return response()->json();
        }
    }
