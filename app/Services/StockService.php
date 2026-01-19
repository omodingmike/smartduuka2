<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Requests\PaginateRequest;
    use App\Models\Ingredient;
    use App\Models\Stock;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\URL;

    class StockService
    {
        public    $items;
        public    $ingredients;
        public    $links;
        protected $stockFilter = [
            'product_name' ,
            'status' ,
            'warehouse_id' ,
        ];


        /**
         * @throws Exception
         */

        public function list(PaginateRequest $request)
        {
            try {
                $filter      = $request->validated(); // Use validated data
                $perPage     = $request->integer( 'per_page' , 10 );
                $isPaginated = $request->boolean( 'paginate' );

                $query = Stock::with( [
                    'product.sellingUnits:id,short_name' ,
                    'product.unit:id,short_name' ,
                    'products' ,
                    'user'
                ] )
                              ->where( 'status' , StockStatus::RECEIVED )
                              ->whereNull( 'user_id' )
                              ->where( 'model_type' , '<>' , Ingredient::class )
                              ->when( $request->warehouse_id , fn($q) => $q->where( 'warehouse_id' , $request->warehouse_id ) )
                    // Dynamic Filters
                              ->where( function ($q) use ($request) {
                        foreach ( $this->stockFilter as $field ) {
                            if ( $value = $request->get( $field ) ) {
                                if ( $field === 'product_name' ) {
                                    $q->whereHas( 'product' , fn($pq) => $pq->where( 'name' , 'like' , "%{$value}%" ) );
                                }
                                else {
                                    $q->where( $field , 'like' , "%{$value}%" );
                                }
                            }
                        }
                    } )
                              ->orderBy( $request->get( 'order_column' , 'id' ) , $request->get( 'order_type' , 'desc' ) );

                // Fetch data
                $stocks = $query->get();

                if ( $stocks->isEmpty() ) {
                    return $isPaginated ? $this->paginate( [] , $perPage ) : [];
                }

                // Grouping Logic
                $groupCriteria = enabledWarehouse()
                    ? fn($item) => $item->product_id . '-' . $item->warehouse_id
                    : fn($item) => $item->product_id . '-' . $item->item_type . '-' . $item->variation_names;

                $processedItems = $stocks->groupBy( $groupCriteria )
                                         ->map( fn($group) => $this->transformStockGroup( $group ) )
                                         ->filter( fn($item) => $item !== NULL && $item[ 'stock' ] > 0 )
                                         ->values();

                if ( $isPaginated ) {
                    return $this->paginate( $processedItems , $perPage , NULL , url( '/api/admin/stock' ) );
                }

                return $processedItems;

            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        protected function transformStockGroup($group)
        {
            $first = $group->first();
            if ( ! $first->product ) return NULL;

            $isPurchasable = $first->product->can_purchasable !== Ask::NO;
            $status        = $first->status; // This is now your Enum object

            return [
                'product_id'               => $first->product_id ,
                'products'                 => $first->products ,
                'stock_status'             => [
                    'value' => $status->value ,
                    'label' => $status->label() ,
                ] ,
                'product_name'             => $first->product->name ,
                'unit'                     => $first->product->unit ,
                'other_unit'               => $first->product->otherUnit ,
                'units_nature'             => $first->product->units_nature ,
                'variation_names'          => $first->variation_names ,
                'status'                   => $first->product->status ,
                'warehouse_id'             => $first->warehouse_id ,
                'reference'                => $first->reference ,
                'delivery'                 => $first->delivery ,
                'system_stock'             => $first->system_stock ,
                'physical_stock'           => $first->physical_stock ,
                'difference'               => $first->difference ,
                'discrepancy'              => $first->discrepancy ,
                'classification'           => $first->classification ,
                'creator'                  => $first->user ,
                'batch'                    => $first->batch ,
                'weight'                   => $first->product->weight ,
                'source_warehouse_id'      => $first->source_warehouse_id ,
                'total'                    => $first->total ,
                'destination_warehouse_id' => $first->destination_warehouse_id ,
                'created_at'               => $first->created_at ,
                'description'              => $first->description ,
                'stock'                    => $isPurchasable ? $group->sum( 'quantity' ) : 'N/C' ,
                'other_stock'              => $isPurchasable ? $group->sum( 'other_quantity' ) : 'N/C' ,
            ];
        }

        public function expiryList(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';
                $stocks      = Stock::with( [ 'product.sellingUnits:id,short_name' , 'product.unit:id,short_name' , 'warehouse:name,id' ] )
                                    ->when( isset( $requests[ 'warehouse_id' ] ) , function ($query) use ($requests) {
                                        $query->where( 'warehouse_id' , $requests[ 'warehouse_id' ] );
                                    } )
                                    ->when( isset( $requests[ 'stock_status' ] ) , function ($query) use ($requests) {
                                        switch ( $requests[ 'stock_status' ] ) {
                                            case 1:
                                                $query->where( 'expiry_date' , '>' , now()->addDays( 30 ) );
                                                break;
                                            case 2:
                                                $query->where( function ($q) {
                                                    $q->where( 'expiry_date' , '>' , now()->endOfDay() )
                                                      ->where( 'expiry_date' , '<=' , now()->addDays( 30 ) );
                                                } );
                                                break;
                                            default:
                                                $query->where( 'expiry_date' , '<=' , now()->copy()->endOfDay() );
                                        }
                                    } )
                                    ->where( 'status' , StockStatus::RECEIVED )
                                    ->where( 'expiry_date' , '<>' , NULL )
                                    ->where( function ($query) use ($requests) {
                                        $query->where( 'model_type' , '<>' , Ingredient::class );
                                        foreach ( $requests as $key => $request ) {
                                            if ( in_array( $key , $this->stockFilter ) ) {
                                                if ( $key == 'product_name' ) {
                                                    $query->whereHas( 'product' , function ($query) use ($request) {
                                                        $query->where( 'name' , 'like' , '%' . $request . '%' );
                                                    } );
                                                }
                                                else {
                                                    $query->where( $key , 'like' , '%' . $request . '%' );
                                                }
                                            }
                                        }
                                    } )->orderBy( $orderColumn , $orderType )->get();

                if ( $method == 'paginate' ) {
                    return $this->paginate( $stocks , $methodValue , NULL , URL::to( '/' ) . '/api/admin/stock/expiryList' );
                }
                return $stocks;

            } catch ( Exception $exception ) {
                info( $exception->getMessage() );
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function transfers(Request $request)
        {
            try {
                $perPage     = $request->integer( 'per_page' , 10 );
                $isPaginated = $request->boolean( 'paginate' );
                $orderColumn = $request->get( 'order_column' , 'id' );
                $orderType   = $request->get( 'order_type' , 'desc' );

                $stocks = Stock::with( [
                    'product.sellingUnits:id,short_name' ,
                    'product.unit:id,short_name' ,
                    'user:id,name' ,
                    'products'
                ] )
                               ->when( $request->warehouse_id , fn($q) => $q->where( 'warehouse_id' , $request->warehouse_id ) )
                               ->when( $request->type , fn($q) => $q->where( 'type' , $request->type ) )
                               ->when( $request->discrepancy , fn($q) => $q->where( 'discrepancy' , $request->discrepancy ) )
                               ->when( $request->classification , fn($q) => $q->where( 'classification' , $request->classification ) )
                    // Reference Prefixes
                               ->when( $request->type == 'transfer' , fn($q) => $q->where( 'reference' , 'like' , 'ST%' ) )
                               ->when( $request->type == 'reconciliation' , fn($q) => $q->where( 'reference' , 'like' , 'RST%' ) )
                    // Search Filters
                               ->where( function ($query) use ($request) {
                        $query->where( 'model_type' , '<>' , Ingredient::class );
                        foreach ( $this->stockFilter as $filterKey ) {
                            if ( $value = $request->get( $filterKey ) ) {
                                if ( $filterKey == 'product_name' ) {
                                    $query->whereHas( 'product' , fn($pq) => $pq->where( 'name' , 'like' , "%{$value}%" ) );
                                }
                                else {
                                    $query->where( $filterKey , 'like' , "%{$value}%" );
                                }
                            }
                        }
                    } )
                               ->orderBy( $orderColumn , $orderType )
                               ->get();

                if ( $stocks->isNotEmpty() ) {
                    // Clear items before mapping to avoid duplication if this is a class property
                    $this->items = [];

                    $stocks->groupBy( 'batch' )->each( function ($group) {
                        $first = $group->first();

                        // Ensure product exists
                        if ( $first && $first->product ) {
                            $status        = $first->status; // This is the StockStatus Enum instance
                            $isPurchasable = $first->product->can_purchasable !== Ask::NO;

                            $this->items[] = [
                                'product_id'               => $first->product_id ,
                                'products'                 => $first->products ,
                                'product_name'             => $first->product->name ,
                                'unit'                     => $first->product->unit ,
                                'weight'                   => $first->product->weight ,
                                'other_unit'               => $first->product->otherUnit ,
                                'units_nature'             => $first->product->units_nature ,
                                'variation_names'          => $first->variation_names ,
                                // Structured Enum Output
                                'stock_status'             => [
                                    'value' => $status->value ,
                                    'label' => $status->label() ,
                                ] ,
                                'status'                   => $status->value , // Keeping raw value for legacy compatibility
                                'warehouse_id'             => $first->warehouse_id ,
                                'reference'                => $first->reference ,
                                'system_stock'             => $first->system_stock ,
                                'physical_stock'           => $first->physical_stock ,
                                'difference'               => $first->difference ,
                                'discrepancy'              => $first->discrepancy ,
                                'classification'           => $first->classification ,
                                'creator'                  => $first->user ,
                                'delivery'                 => $first->delivery ,
                                'source_warehouse_id'      => $first->source_warehouse_id ,
                                'batch'                    => $first->batch ,
                                'total'                    => $first->total ,
                                'destination_warehouse_id' => $first->destination_warehouse_id ,
                                'created_at'               => $first->created_at ,
                                'description'              => $first->description ,
                                'stock'                    => $isPurchasable ? $group->sum( 'quantity' ) : 'N/C' ,
                                'other_stock'              => $isPurchasable ? $group->sum( 'other_quantity' ) : 'N/C' ,
                            ];
                        }
                    } );
                }
                else {
                    $this->items = [];
                }

                if ( $isPaginated ) {
                    return $this->paginate( $this->items , $perPage , NULL , url( '/api/admin/stock' ) );
                }

                return $this->items;

            } catch ( Exception $exception ) {
                Log::error( 'Transfer List Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function transfer(Request $request)
        {
            try {
                $stocks = Stock::with( [ 'product.sellingUnits:id,code' , 'product.unit:id,code' ] )
                               ->where( 'reference' , 'like' , 'ST%' )
                               ->where( 'batch' , $request->batch )
                               ->where( function ($query) {
                                   $query->where( 'model_type' , '<>' , Ingredient::class );
                               } )->get();
                return $stocks->unique( 'product_id' )->values();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listIngredients(PaginateRequest $request)
        {
//        try {
            $requests    = $request->all();
            $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
            $orderColumn = $request->get( 'order_column' ) ?? 'id';
            $orderType   = $request->get( 'order_type' ) ?? 'desc';

            $stocks = Stock::with( 'item' )->where( 'status' , Status::ACTIVE )
                           ->where( function ($query) use ($requests) {
                               $query->where( 'item_type' , Ingredient::class );
                               foreach ( $requests as $key => $request ) {
                                   if ( in_array( $key , $this->stockFilter ) ) {
                                       if ( $key == 'product_name' ) {
                                           $query->whereHas( 'item' , function ($query) use ($request) {
                                               $query->where( 'name' , 'like' , '%' . $request . '%' );
                                           } )->get();
                                       }
                                       else {
                                           $query->where( $key , 'like' , '%' . $request . '%' );
                                       }
                                   }
                               }
                           } )->orderBy( $orderColumn , $orderType )->get();
            if ( ! blank( $stocks ) ) {
                $stocks->groupBy( 'item_id' )?->map( function ($item) {
//                    $item->groupBy('item_id')?->map(function ($item) {

                    $this->ingredients[] = [
                        'quantity'       => $item->sum( 'quantity' ) ,
                        'quantity_alert' => $item->first()[ 'item' ][ 'quantity_alert' ] ,
                        'name'           => $item->first()[ 'item' ][ 'name' ] ,
                        'unit'           => $item->first()[ 'item' ][ 'unit' ] ,
                        'status'         => $item->first()[ 'item' ][ 'status' ] ,
                    ];
//                    });
                } );
                return $this->ingredients;
            }
            else {
                $this->items = [];
            }

            if ( $method == 'paginate' ) {
                return $this->paginate( $this->items , $methodValue , NULL , URL::to( '/' ) . '/api/admin/itemStock' );
            }

            return $this->items;
//        } catch (Exception $exception) {
//            Log::info($exception->getMessage());
//            throw new Exception($exception->getMessage(), 422);
//        }
        }

        public function paginate(
            $items ,
            $perPage = 15 ,
            $page = NULL ,
            $baseUrl = NULL ,
            $options = []
        )
        {
            $page = $page ?: ( Paginator::resolveCurrentPage() ?: 1 );

            $items = $items instanceof Collection ?
                $items : Collection::make( $items );

            $lap = new LengthAwarePaginator(
                $items->forPage( $page , $perPage ) ,
                $items->count() ,
                $perPage ,
                $page ,
                $options
            );

            if ( $baseUrl ) {
                $lap->setPath( $baseUrl );
            }
            return $lap;
        }
    }
