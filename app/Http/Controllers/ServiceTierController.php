<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ServiceTierRequest;
    use App\Http\Resources\ServiceTierResource;
    use App\Models\ServiceTier;

    class ServiceTierController extends Controller
    {
        public function index()
        {
            return ServiceTierResource::collection( ServiceTier::all() );
        }

        public function store(ServiceTierRequest $request)
        {
            return new ServiceTierResource( ServiceTier::create( $request->validated() ) );
        }

        public function show(ServiceTier $serviceTier)
        {
            return new ServiceTierResource( $serviceTier );
        }

        public function update(ServiceTierRequest $request , ServiceTier $serviceTier)
        {
            $serviceTier->update( $request->validated() );

            return new ServiceTierResource( $serviceTier );
        }

        public function destroy(ServiceTier $serviceTier)
        {
            $serviceTier->delete();

            return response()->json();
        }
    }
