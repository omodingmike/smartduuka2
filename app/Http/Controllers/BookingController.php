<?php

    namespace App\Http\Controllers;

    use App\Enums\BookingStatus;
    use App\Enums\StockStatus;
    use App\Http\Requests\BookingRequest;
    use App\Http\Resources\BookingResource;
    use App\Models\Booking;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use App\Models\Warehouse;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class BookingController extends Controller
    {
        public function index(Request $request)
        {
            $q = Booking::with( [ 'addsOn' , 'service' , 'activityLogs' , 'customer' ] )->latest();
            return BookingResource::collection( $q->paginate() );
        }

        public function store(BookingRequest $request)
        {
            return DB::transaction( function () use ($request) {
                $booking = Booking::create( [
                    'service_id'  => $request->input( 'service_id' ) ,
                    'customer_id' => $request->input( 'customer_id' ) ,
                    'date'        => $request->input( 'date' ) ,
                    'status'      => $request->input( 'status' ) ,
                    'total'       => $request->input( 'total' ) ,
                    'notes'       => $request->input( 'notes' ) ?? '' ,
                ] );

                $adds_ons      = json_decode( $request->adds_on , TRUE );
                $activity_logs = json_decode( $request->activity_logs , TRUE );

                foreach ( $adds_ons as $adds_on ) {
                    $booking->addsOn()->create( [
                        'booking_id'        => $booking->id ,
                        'service_add_on_id' => $adds_on[ 'id' ]
                    ] );
                }

                foreach ( $activity_logs as $activity_log ) {
                    $booking->activityLogs()->create( [
                        'booking_id' => $booking->id ,
                        'note'       => $activity_log[ 'action' ] ,
                        'created_at' => $activity_log[ 'created_at' ] ,
                        'user_id'    => auth()->id() ,
                        'status'     => $booking->status ,
                    ] );
                }
                return response()->json();
            } );
        }

        public function show(Booking $booking)
        {
            return new BookingResource( $booking );
        }


        public function update(BookingRequest $request , Booking $booking)
        {
            return DB::transaction( function () use ($request , $booking) {

                $status = $request->integer( 'status' );
                $booking->update( [
                    'customer_id' => $request->input( 'customer_id' ) ,
                    'service_id'  => $request->input( 'service_id' ) ,
                    'date'        => $request->input( 'date' ) ,
                    'status'      => $status ,
                    'total'       => $request->input( 'total' ) ,
                    'notes'       => $request->input( 'notes' ) ?? '' ,
                ] );

                if ( $status == BookingStatus::COMPLETED->value ) {
                    $items = $booking->service->items;
                    foreach ( $items as $item ) {
                        $p            = Product::find( $item[ 'item_id' ] );
                        $is_variation = isset( $item[ 'variation_id' ] );
                        $variation    = NULL;
                        $targetModel  = $p;
                        $targetClass  = Product::class;
                        $itemId       = $item[ 'item_id' ];

                        if ( $is_variation ) {
                            $variation_id = $item[ 'variation_id' ];
                            $variation    = ProductVariation::find( $variation_id );
                            if ( $variation ) {
                                $targetModel = $variation;
                                $targetClass = ProductVariation::class;
                                $itemId      = $variation->id;
                            }
                        }
                        if ( $targetModel->stock < $item[ 'quantity' ] ) {
                            $name = $is_variation ? $p->name . ' (' . $variation?->productAttributeOption?->name . ')' : $p->name;
                            throw  new Exception( "{$name} stock not enough" );
                        }
                        $stock = Stock::where( [
                            'item_id'      => $itemId ,
                            'item_type'    => $targetClass ,
                            'status'       => StockStatus::RECEIVED ,
                            'warehouse_id' => Warehouse::first()->id
                        ] )->first();
                        $stock->decrement( 'quantity' , $item[ 'quantity' ] );
                    }
                }

                $adds_ons      = json_decode( $request->adds_on , TRUE );
                $activity_logs = json_decode( $request->activity_logs , TRUE );

                $booking->addsOn()->delete();
                foreach ( $adds_ons as $adds_on ) {
                    $booking->addsOn()->create( [
                        'booking_id'        => $booking->id ,
                        'service_add_on_id' => $adds_on[ 'id' ] ,
                    ] );
                }

                $booking->activityLogs()->delete();
                foreach ( $activity_logs as $activity_log ) {
                    $booking->activityLogs()->create( [
                        'booking_id' => $booking->id ,
                        'note'       => $activity_log[ 'action' ] ,
                        'created_at' => $activity_log[ 'created_at' ] ?? $activity_log[ 'date' ] ?? now() ,
                        'user_id'    => auth()->id() ,
                        'status'     => $booking->status ,
                    ] );
                }

                return new BookingResource( $booking->load( [ 'addsOn' , 'service' , 'activityLogs' ] ) );
            } );
        }

        public function destroy(Request $request)
        {
            return DB::transaction( function () use ($request) {
                $bookings = Booking::whereIn( 'id' , $request->ids )->get();
                foreach ( $bookings as $booking ) {
                    $booking->addsOn()->delete();
                    $booking->activityLogs()->delete();
                    $booking->delete();
                }
                return response()->json();
            } );
        }
    }
