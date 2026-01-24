<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StoreWarehouseRequest;
    use App\Http\Requests\UpdateWarehouseRequest;
    use App\Http\Resources\WarehouseResource;
    use App\Models\Warehouse;
    use App\Services\StockService;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class WarehouseController extends Controller
    {
        public array  $warehouseFilter = [ 'name' , 'email' , 'location' , 'phone' ];
        public object $warehouse;

        public function index(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'asc';

                $warehouses = Warehouse::where( function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->warehouseFilter ) && $request ) {
                            $query->where( $key , 'like' , '%' . $request . '%' );
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );

                return WarehouseResource::collection( $warehouses );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function store(StoreWarehouseRequest $request)
        {
            try {
                Warehouse::create( $request->validated() );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function show(Warehouse $warehouse)
        {
            try {
                return new WarehouseResource( $warehouse );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function update(UpdateWarehouseRequest $request , Warehouse $warehouse)
        {
            try {
                DB::transaction( function () use ($warehouse , $request) {
                    $this->warehouse           = $warehouse;
                    $this->warehouse->name     = $request->name;
                    $this->warehouse->email    = $request->email;
                    $this->warehouse->phone    = $request->phone;
                    $this->warehouse->location = $request->location;
                    $this->warehouse->manager  = $request->manager;
                    $this->warehouse->status   = $request->status;
                    $this->warehouse->capacity = $request->capacity;
                    $this->warehouse->save();
                } );
                return $this->warehouse;

            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function destroy(Request $request)
        {
            Warehouse::destroy( $request->ids );
//            try {
//                if ( ! $warehouse->deletable ) {
//                    throw new Exception( 'This warehouse is not deletable' , 422 );
//                }
//                else {
//                    $warehouse->delete();
//                }
//            } catch ( Exception $exception ) {
//                Log::info( $exception->getMessage() );
//                throw new Exception( $exception->getMessage() , 422 );
//            }
        }
    }
