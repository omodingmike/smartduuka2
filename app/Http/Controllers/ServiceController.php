<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ServiceRequest;
    use App\Http\Resources\ServiceResource;
    use App\Models\Product;
    use App\Models\Service;
    use App\Models\ServiceAddOn;
    use App\Models\ServiceItem;
    use App\Models\ServiceTier;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class ServiceController extends Controller
    {
        public function index(Request $request)
        {
            $paginate = $request->boolean( 'paginate' );
            $s        = Service::with( [ 'serviceCategory' , 'items.item' , 'addOns' , 'tiers' ] )
                               ->withSum( 'addOns' , 'price' )
                               ->latest();
            return ServiceResource::collection( $paginate ? $s->paginate() : $s->get() );
        }

        public function store(ServiceRequest $request)
        {
            return DB::transaction( function () use ($request) {
                $service           = Service::create( [
                    'name'                => $request->input( 'name' ) ,
                    'service_category_id' => $request->input( 'service_category_id' ) ,
                    'base_price'          => $request->input( 'base_price' ) ,
                    'duration'            => $request->input( 'duration' , '' ) ,
                    'description'         => $request->input( 'description' , '' ) ,
                    'type'                => $request->input( 'type' ) ,
                    'status'              => $request->input( 'status' ) ,
                ] );
                $stockConsumptions = json_decode( $request->stockConsumption , TRUE );
                $addons            = json_decode( $request->addons , TRUE );
                $tiers             = json_decode( $request->tiers , TRUE );

                foreach ( $addons as $addon ) {
                    ServiceAddOn::create( [
                        'name'       => $addon[ 'name' ] ,
                        'price'      => $addon[ 'price' ] ,
                        'service_id' => $service->id
                    ] );
                }
                foreach ( $tiers as $tier ) {
                    ServiceTier::create( [
                        'name'       => $tier[ 'name' ] ,
                        'features'   => $tier[ 'description' ] ,
                        'price'      => $tier[ 'price' ] ,
                        'service_id' => $service->id
                    ] );
                }

                foreach ( $stockConsumptions as $stock_consumption ) {
                    ServiceItem::create( [
                        'item_id'    => $stock_consumption[ 'productId' ] ,
                        'item_type'  => Product::class ,
                        'quantity'   => $stock_consumption[ 'quantity' ] ,
                        'service_id' => $service->id ,
                        'price_id'   => $stock_consumption[ 'price_id' ] ,
                        'price_type' => $stock_consumption[ 'price_type' ] ,
                        'price'      => $stock_consumption[ 'unitCost' ] ,
                        'total'      => $stock_consumption[ 'quantity' ] * $stock_consumption[ 'unitCost' ] ,
                    ] );
                }
                return response()->json();
            } );
        }

        public function update(ServiceRequest $request , Service $service)
        {
            return DB::transaction( function () use ($request , $service) {
                $service->update( [
                    'name'                => $request->input( 'name' ) ,
                    'service_category_id' => $request->input( 'service_category_id' ) ,
                    'base_price'          => $request->input( 'base_price' ) ,
                    'duration'            => $request->input( 'duration' ) ,
                    'description'         => $request->input( 'description' ) ,
                    'type'                => $request->input( 'type' ) ,
                    'status'              => $request->input( 'status' ) ,
                ] );

                $stockConsumptions = json_decode( $request->stockConsumption , TRUE );
                $addons            = json_decode( $request->addons , TRUE );
                $tiers             = json_decode( $request->tiers , TRUE );

                $service->addOns()->delete();
                foreach ( $addons as $addon ) {
                    ServiceAddOn::create( [
                        'name'       => $addon[ 'name' ] ,
                        'price'      => $addon[ 'price' ] ,
                        'service_id' => $service->id ,
                    ] );
                }

                $service->tiers()->delete();
                foreach ( $tiers as $tier ) {
                    ServiceTier::create( [
                        'name'       => $tier[ 'name' ] ,
                        'features'   => $tier[ 'description' ] ,
                        'price'      => $tier[ 'price' ] ,
                        'service_id' => $service->id ,
                    ] );
                }

                $service->items()->delete();
                foreach ( $stockConsumptions as $stock_consumption ) {
                    ServiceItem::create( [
                        'item_id'    => $stock_consumption[ 'productId' ] ,
                        'item_type'  => Product::class ,
                        'quantity'   => $stock_consumption[ 'quantity' ] ,
                        'service_id' => $service->id ,
                        'price_id'   => $stock_consumption[ 'price_id' ] ,
                        'price_type' => $stock_consumption[ 'price_type' ] ,
                        'price'      => $stock_consumption[ 'unitCost' ] ,
                        'total'      => $stock_consumption[ 'quantity' ] * $stock_consumption[ 'unitCost' ] ,
                    ] );
                }

                return response()->json();
            } );
        }

        public function destroy(Request $request)
        {
            Service::destroy( $request->ids );

            return response()->json();
        }
    }
