<?php

    namespace App\Http\Controllers;

    use App\Enums\CleaningOrderStatus;
    use App\Enums\SettingsKeyEnum;
    use App\Http\Requests\CleaningOrderRequest;
    use App\Http\Resources\CleaningOrderResource;
    use App\Models\CleaningOrder;
    use App\Models\CleaningOrderItem;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class CleaningOrderController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            $query = CleaningOrder::with( [
                'items.cleaningService' ,
                'paymentMethod' ,
                'cleaningServiceCustomer'
            ] );
            return CleaningOrderResource::collection( $this->filter( $query , $request ) );
        }

        /**
         * @throws \Throwable
         */
        public function store(CleaningOrderRequest $request)
        {
            $data = $request->validated();
            info( $data );
            $prefix = settingValue( 'order_prefix' , SettingsKeyEnum::CLEANING ) ?? 'SDCC-';
            return DB::transaction( function () use ($prefix , $data) {
                $order = CleaningOrder::create( [
                    'order_id'                     => $prefix . time() ,
                    'cleaning_service_customer_id' => $data[ 'cleaning_service_customer_id' ] ,
                    'total'                        => $data[ 'total' ] ,
                    'date'                         => $data[ 'date' ] ,
                    'service_method'               => $data[ 'service_method' ] ,
                    'subtotal'                     => $data[ 'subtotal' ] ,
                    'tax'                          => $data[ 'tax' ] ,
                    'discount'                     => $data[ 'discount' ] ,
                    'payment_method_id'            => $data[ 'payment_method_id' ] ,
                    'paid'                         => $data[ 'paid' ] ,
                    'balance'                      => $data[ 'balance' ] ,
                    'status'                       => CleaningOrderStatus::PendingAcceptance->value ,
                ] );
                $items = collect( json_decode( $data[ 'items' ] , TRUE ) );
                foreach ( $items as $value ) {
                    $orderItem = CleaningOrderItem::create( [
                        'cleaning_service_id' => $value[ 'service' ][ 'id' ] ,
                        'description'         => $value[ 'description' ] ,
                        'quantity'            => $value[ 'quantity' ] ,
                        'notes'               => $value[ 'notes' ] ,
                    ] );

                    $order->items()->attach( $orderItem->id );
                }
                return new CleaningOrderResource( $order->load( [ 'items.cleaningService' ] ) );
            } );
        }

        public function show(CleaningOrder $cleaningOrder)
        {
            return new CleaningOrderResource( $cleaningOrder );
        }

        public function update(CleaningOrderRequest $request , CleaningOrder $cleaningOrder)
        {
            $cleaningOrder->update( $request->validated() );

            return new CleaningOrderResource( $cleaningOrder );
        }

        public function destroy(CleaningOrder $cleaningOrder)
        {
            $cleaningOrder->delete();

            return response()->json();
        }
    }
