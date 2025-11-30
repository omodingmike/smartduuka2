<?php

    namespace App\Http\Controllers;

    use App\Enums\CleaningOrderStatus;
    use App\Enums\MediaEnum;
    use App\Enums\SettingsKeyEnum;
    use App\Http\Requests\CleaningOrderRequest;
    use App\Http\Requests\ClientCleaningOrderRequest;
    use App\Http\Resources\CleaningOrderResource;
    use App\Models\CleaningOrder;
    use App\Models\CleaningOrderItem;
    use App\Models\CleaningServiceCustomer;
    use App\Traits\HasAdvancedFilter;
    use App\Traits\SaveMedia;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class CleaningOrderController extends Controller
    {
        use HasAdvancedFilter , SaveMedia;

        public function index(Request $request)
        {
            $query = CleaningOrder::with( [
                'items.cleaningService' ,
                'paymentMethod' ,
                'cleaningServiceCustomer'
            ] );
            return CleaningOrderResource::collection( $this->filter( $query , $request ) );
        }

        public function order(Request $request)
        {
            $order_id = $request->input( 'order_id' );

            $order = CleaningOrder::with( [
                'items.cleaningService' ,
                'paymentMethod' ,
                'cleaningServiceCustomer'
            ] )->where( 'order_id' , $order_id )->first();
            return new CleaningOrderResource( $order ?? [] );
        }

        /**
         * @throws \Throwable
         */
        public function store(CleaningOrderRequest $request)
        {
            $data   = $request->validated();
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
                    'status'                       => CleaningOrderStatus::Accepted->value ,
                ] );
                if ( isset( $data[ 'address' ] ) && $data[ 'address' ] ) {
                    $order->update( [ 'address' => $data[ 'address' ] ] );
                }
                $items = collect( json_decode( $data[ 'items' ] , TRUE ) );
                foreach ( $items as $value ) {
                    $notes     = $data[ 'notes' ] ?? '';
                    $orderItem = CleaningOrderItem::create( [
                        'cleaning_service_id' => $value[ 'service' ][ 'id' ] ,
                        'description'         => $value[ 'description' ] ,
                        'quantity'            => $value[ 'quantity' ] ,
                        'notes'               => $notes
                    ] );
                    if ( isset( $data[ 'notes' ] ) && $data[ 'notes' ] ) {
                        $orderItem->update( [ 'notes' => $data[ 'notes' ] ] );
                    }

                    $order->items()->attach( $orderItem->id );
                }
                return new CleaningOrderResource( $order->load( [ 'items.cleaningService' , 'paymentMethod' , 'cleaningServiceCustomer' ] ) );
            } );
        }

        public function storeClient(ClientCleaningOrderRequest $request)
        {
            $data           = $request->validated();
            $prefix         = settingValue( 'order_prefix' , SettingsKeyEnum::CLEANING ) ?? 'SDCC-';
            $customer       = json_decode( $data[ 'customer' ] , TRUE );
            $order_customer = CleaningServiceCustomer::firstOrCreate( [
                'phone' => $customer[ 'phone' ] ,
                'name'  => $customer[ 'name' ] ,
            ] );
            return DB::transaction( function () use ($prefix , $data , $order_customer , $request) {
                $order = CleaningOrder::create( [
                    'order_id'                     => $prefix . time() ,
                    'cleaning_service_customer_id' => $order_customer->id ,
                    'total'                        => $data[ 'total' ] ,
                    'date'                         => $data[ 'date' ] ,
                    'service_method'               => $data[ 'service_method' ] ,
                    'subtotal'                     => $data[ 'subtotal' ] ,
                    'tax'                          => $data[ 'tax' ] ,
                    'discount'                     => $data[ 'discount' ] ,
                    'paid'                         => 0 ,
                    'balance'                      => $data[ 'balance' ] ,
                    'status'                       => CleaningOrderStatus::PendingAcceptance->value ,
                ] );

                $this->saveMedia( $request , $order , MediaEnum::ORDERS_COLLECTION );
                if ( isset( $data[ 'address' ] ) && $data[ 'address' ] ) {
                    $order->update( [ 'address' => $data[ 'address' ] ] );
                }
                $items = collect( json_decode( $data[ 'items' ] , TRUE ) );
                foreach ( $items as $value ) {
                    $notes     = $data[ 'notes' ] ?? '';
                    $orderItem = CleaningOrderItem::create( [
                        'cleaning_service_id' => $value[ 'service' ][ 'id' ] ,
                        'description'         => $value[ 'description' ] ,
                        'quantity'            => $value[ 'quantity' ] ,
                        'notes'               => $notes
                    ] );
                    $order->items()->attach( $orderItem->id );
                }
                return new CleaningOrderResource( $order->load( [ 'items.cleaningService' , 'paymentMethod' , 'cleaningServiceCustomer' ] ) );
            } );
        }

        public function show(CleaningOrder $cleaningOrder)
        {
            return new CleaningOrderResource( $cleaningOrder );
        }

        public function update(Request $request , CleaningOrder $cleaningOrder)
        {
            $cleaningOrder->update( [
                'status' => $request->status
            ] );

            return new CleaningOrderResource( $cleaningOrder );
        }

        public function destroy(Request $request)
        {
            CleaningOrder::destroy( $request->ids );
            return response()->json();
        }
    }
