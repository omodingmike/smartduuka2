<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\SubscriptionPlanRequest;
    use App\Http\Resources\BillingCycleResource;
    use App\Http\Resources\SubscriptionPlanResource;
    use App\Models\BillingCycle;
    use App\Models\SubscriptionPlan;

    class SubscriptionPlanController extends Controller
    {
        public function index()
        {
            return SubscriptionPlanResource::collection( SubscriptionPlan::all() );
        }

        public function billingCycles()
        {
            return BillingCycleResource::collection( BillingCycle::all() );
        }

        public function store(SubscriptionPlanRequest $request)
        {
            return new SubscriptionPlanResource( SubscriptionPlan::create( $request->validated() ) );
        }

        public function show(SubscriptionPlan $subscriptionPlan)
        {
            return new SubscriptionPlanResource( $subscriptionPlan );
        }

        public function update(SubscriptionPlanRequest $request , SubscriptionPlan $subscriptionPlan)
        {
            $subscriptionPlan->update( $request->validated() );

            return new SubscriptionPlanResource( $subscriptionPlan );
        }

        public function destroy(SubscriptionPlan $subscriptionPlan)
        {
            $subscriptionPlan->delete();

            return response()->json();
        }
    }
