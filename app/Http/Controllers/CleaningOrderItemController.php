<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningOrderItemRequest;
    use App\Http\Resources\CleaningOrderItemResource;
    use App\Models\CleaningOrderItem;

    class CleaningOrderItemController extends Controller
    {
        public function index()
        {
            return CleaningOrderItemResource::collection( CleaningOrderItem::all() );
        }

        public function store(CleaningOrderItemRequest $request)
        {
            return new CleaningOrderItemResource( CleaningOrderItem::create( $request->validated() ) );
        }

        public function show(CleaningOrderItem $cleaningOrderItem)
        {
            return new CleaningOrderItemResource( $cleaningOrderItem );
        }

        public function update(CleaningOrderItemRequest $request , CleaningOrderItem $cleaningOrderItem)
        {
            $cleaningOrderItem->update( $request->validated() );

            return new CleaningOrderItemResource( $cleaningOrderItem );
        }

        public function destroy(CleaningOrderItem $cleaningOrderItem)
        {
            $cleaningOrderItem->delete();

            return response()->json();
        }
    }
