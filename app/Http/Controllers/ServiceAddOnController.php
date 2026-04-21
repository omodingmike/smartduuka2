<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ServiceAddOnRequest;
    use App\Http\Resources\ServiceAddOnResource;
    use App\Models\ServiceAddOn;

    class ServiceAddOnController extends Controller
    {
        public function index()
        {
            return ServiceAddOnResource::collection( ServiceAddOn::all() );
        }

        public function store(ServiceAddOnRequest $request)
        {
            return new ServiceAddOnResource( ServiceAddOn::create( $request->validated() ) );
        }

        public function show(ServiceAddOn $serviceAddOn)
        {
            return new ServiceAddOnResource( $serviceAddOn );
        }

        public function update(ServiceAddOnRequest $request , ServiceAddOn $serviceAddOn)
        {
            $serviceAddOn->update( $request->validated() );

            return new ServiceAddOnResource( $serviceAddOn );
        }

        public function destroy(ServiceAddOn $serviceAddOn)
        {
            $serviceAddOn->delete();

            return response()->json();
        }
    }
