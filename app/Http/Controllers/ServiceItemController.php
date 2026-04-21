<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ServiceItemRequest;
    use App\Http\Resources\ServiceItemResource;
    use App\Models\ServiceItem;

    class ServiceItemController extends Controller
    {
        public function index()
        {
            return ServiceItemResource::collection( ServiceItem::all() );
        }

        public function store(ServiceItemRequest $request)
        {
            return new ServiceItemResource( ServiceItem::create( $request->validated() ) );
        }

        public function show(ServiceItem $serviceItem)
        {
            return new ServiceItemResource( $serviceItem );
        }

        public function update(ServiceItemRequest $request , ServiceItem $serviceItem)
        {
            $serviceItem->update( $request->validated() );

            return new ServiceItemResource( $serviceItem );
        }

        public function destroy(ServiceItem $serviceItem)
        {
            $serviceItem->delete();

            return response()->json();
        }
    }
