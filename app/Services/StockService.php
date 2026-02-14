<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Requests\PaginateRequest;
    use App\Models\Ingredient;
    use App\Models\ProductAttribute;
    use App\Models\ProductAttributeOption;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
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

        public function list1(Request $request)
        {
            try {
                $perPage      = $request->integer( 'per_page' , 10 );
                $isPaginated  = $request->boolean( 'paginate' );
                $warehouse_id = $request->warehouse_id;

                $stocks       = $this->stockQuery( $request )
                                     ->where( 'status' , StockStatus::RECEIVED )
                                     ->when( $warehouse_id , fn($q) => $q->where( 'warehouse_id' , $warehouse_id ) )
                                     ->get();

                if ( $stocks->isEmpty() ) {
                    return $isPaginated ? $this->paginate( [] , $perPage ) : [];
                }

                $groupCriteria = enabledWarehouse()
                    ? fn($item) => $item->product_id . '-' . $item->warehouse_id . '-' . $item->variation_id
                    : fn($item) => $item->product_id . '-' . $item->item_type . '-' . $item->variation_names . '-' . $item->variation_id;

                $processedItems = $stocks->groupBy( $groupCriteria )
                                         ->map( fn($group) => $this->transformStockGrouped( $group ) )
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
        public function list(Request $request)
        {
            try {
                $perPage      = $request->integer('per_page', 10);
                $isPaginated  = $request->boolean('paginate');
                $warehouse_id = $request->warehouse_id;

                $stocks = $this->stockQuery($request)
                               ->where('status', StockStatus::RECEIVED)
                               ->when($warehouse_id, fn($q) => $q->where('warehouse_id', $warehouse_id))
                               ->get();

                if ($stocks->isEmpty()) {
                    return $isPaginated ? $this->paginate([], $perPage) : [];
                }

                $groupCriteria = function($item) {
                    $wh = enabledWarehouse() ? $item->warehouse_id : 'global';
                    return $item->product_id . '-' . $wh . '-' . ($item->variation_id ?? 'parent');
                };

                $processedItems = $stocks->groupBy($groupCriteria)
                                         ->map(fn($group) => $this->transformStockGrouped($group))
                                         ->filter(fn($item) => $item !== NULL && $item['stock'] !== 0)
                                         ->values();

                return $isPaginated ? $this->paginate($processedItems, $perPage, NULL, url('/api/admin/stock')) : $processedItems;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        private function stockQuery(Request $request) : Builder
        {
            return Stock::with( [
                'stockProducts.item' ,
                'user',
                'product'
            ] )
                        ->whereNull( 'user_id' )
                        ->where( 'model_type' , '<>' , Ingredient::class )
                        ->when( $request->warehouse_id , fn($q) => $q->where( 'warehouse_id' , $request->warehouse_id ) )
                        ->orderBy( 'created_at' , 'desc' );
        }
        private function stockQuery1(Request $request) : Builder
        {
            return Stock::with( [
                'stockProducts.item' ,
                'user'
            ] )
                        ->whereNull( 'user_id' )
                        ->where( 'model_type' , '<>' , Ingredient::class )
                        ->when( $request->warehouse_id , fn($q) => $q->where( 'warehouse_id' , $request->warehouse_id ) )
                        ->orderBy( 'created_at' , 'desc' );
        }

        public function takings(Request $request)
        {
            try {
                $perPage     = $request->integer( 'per_page' , 10 );
                $isPaginated = $request->boolean( 'paginate' );
                $stocks      = $this->stockQuery( $request )->get();
                if ( $stocks->isEmpty() ) {
                    return $isPaginated ? $this->paginate( [] , $perPage ) : [];
                }
//                $groupCriteria  = enabledWarehouse()
//                    ? fn($item) => $item->product_id . '-' . $item->warehouse_id
//                    : fn($item) => $item->product_id . '-' . $item->item_type . '-' . $item->variation_names;
//                $processedItems = $stocks->groupBy( $groupCriteria )
//                                         ->map( fn($group) => $this->transformStockGroup( $group ) )
//                                         ->filter( fn($item) => $item !== NULL && $item[ 'stock' ] > 0 )
//                                         ->values();
                if ( $isPaginated ) {
                    return $this->paginate( $stocks , $perPage , NULL , url( '/api/admin/stock' ) );
                }
                return $stocks;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        protected function transformStockGrouped1($group)
        {
            $first = $group->first();
            if ( ! $first->product ) return NULL;

            $isPurchasable = $first->product->can_purchasable !== Ask::NO;
            $status        = $first->status;

            $variationNames = $first->variation_names;
            if ($first->variation_id) {
                $variation = ProductVariation::with('productAttributeOption.productAttribute')->find($first->variation_id);
                if ($variation && $variation->productAttributeOption) {
                    $variationNames = $variation->productAttributeOption->productAttribute->name . '(' . $variation->productAttributeOption->name . ')';
                }
            }

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
                'variation_names'          => $variationNames ,
                'variation_id'             => $first->variation_id ,
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
                'product_attribute_id'        => $first->product_attribute_id ,
                'product_attribute_option_id' => $first->product_attribute_option_id ,
                'attribute'                   => $first->product_attribute_id ? ProductAttribute::find($first->product_attribute_id) : null,
                'attribute_option'            => $first->product_attribute_option_id ? ProductAttributeOption::find($first->product_attribute_option_id) : null,
            ];
        }

        protected function transformStockGrouped($group)
        {
            $first = $group->first();
            if (!$first->product) return NULL;

            $isPurchasable = $first->product->can_purchasable !== Ask::NO;
            $productName   = $first->product->name;
            $variationNames = $productName;

            if ($first->variation_id) {
                // Dynamic construction using recursive ancestors to prevent "renaming" issues
                $variation = ProductVariation::with([
                    'ancestors.productAttributeOption.productAttribute',
                    'productAttributeOption.productAttribute'
                ])->find($first->variation_id);

                if ($variation) {
                    // Reconstruct the path: ancestors + current node, filtered for valid attributes
                    $path = $variation->ancestors->push($variation)->filter(fn($node) => !is_null($node->product_attribute_option_id));

                    if ($path->isNotEmpty()) {
                        $details = $path->map(function($node) {
                            $attr = $node->productAttributeOption->productAttribute->name ?? 'Attr';
                            $opt  = $node->productAttributeOption->name ?? 'Option';
                            return "$attr :: $opt";
                        })->implode(' > ');

                        $variationNames = "$productName - $details";
                    }
                }
            }

            return [
                'product_id'               => $first->product_id,
                'products'                 => $first->products,
                'stock_status'             => ['value' => $first->status->value, 'label' => $first->status->label()],
                'product_name'             => $productName,
                'unit'                     => $first->product->unit,
                'other_unit'               => $first->product->otherUnit,
                'units_nature'             => $first->product->units_nature,
                'variation_names'          => $variationNames,
                'variation_id'             => $first->variation_id,
                'status'                   => $first->product->status,
                'warehouse_id'             => $first->warehouse_id,
                'reference'                => $first->reference,
                'delivery'                 => $first->delivery,
                'system_stock'             => $first->system_stock,
                'physical_stock'           => $first->physical_stock,
                'difference'               => $first->difference,
                'discrepancy'              => $first->discrepancy,
                'classification'           => $first->classification,
                'creator'                  => $first->user,
                'batch'                    => $first->batch,
                'weight'                   => $first->product->weight,
                'source_warehouse_id'      => $first->source_warehouse_id,
                'total'                    => $first->total,
                'destination_warehouse_id' => $first->destination_warehouse_id,
                'created_at'               => $first->created_at,
                'description'              => $first->description,
                'stock'                    => $isPurchasable ? $group->sum('quantity') : 'N/C',
                'other_stock'              => $isPurchasable ? $group->sum('other_quantity') : 'N/C',
                'product_attribute_id'        => $first->product_attribute_id,
                'product_attribute_option_id' => $first->product_attribute_option_id,
                'attribute'                   => $first->product_attribute_id ? \App\Models\ProductAttribute::find($first->product_attribute_id) : null,
                'attribute_option'            => $first->product_attribute_option_id ? \App\Models\ProductAttributeOption::find($first->product_attribute_option_id) : null,
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
                $type        = $request->type;
                $stocks      = $this->stockQuery( $request )
                                    ->when( $type , fn($q) => $q->where( 'type' , $type ) )
                                    ->get();

                $processedItems = $stocks->groupBy( 'batch' )
                                         ->map( fn($group , $batch) => $this->groupedStock( $group , $batch ) )
                                         ->values();

                if ( $isPaginated ) {
                    return $this->paginate( $processedItems , $perPage , NULL , url( '/api/admin/stock/transfers' ) );
                }

                return $processedItems;
            } catch ( Exception $exception ) {
                Log::error( 'Transfer List Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        private function groupedStock($group , $batch)
        {
            $first = $group->first();

            $productsWithQuantity = $group->map( function ($stock) {
                $product = $stock->product;
                if ( $product ) {
                    $product->transfer_quantity = $stock->quantity;
                    $product->request_quantity  = $stock->request_quantity;
                    $product->approve_quantity  = $stock->approve_quantity;
                }
                return $product;
            } )->filter()->values();

            return (object) [
                'id'                       => $first->id ,
                'batch'                    => $batch ,
                'reference'                => $first->reference ,
                'products'                 => $productsWithQuantity ,
                'user'                     => $first->user ,
                'warehouse_id'             => $first->warehouse_id ,
                'source_warehouse_id'      => $first->source_warehouse_id ,
                'destination_warehouse_id' => $first->destination_warehouse_id ,
                'price'                    => $group->sum( 'price' ) ,
                'quantity'                 => $group->sum( 'quantity' ) ,
                'request_quantity'         => $group->sum( 'request_quantity' ) ,
                'approve_quantity'         => $group->sum( 'approve_quantity' ) ,
                'discount'                 => $group->sum( 'discount' ) ,
                'tax'                      => $group->sum( 'tax' ) ,
                'subtotal'                 => $group->sum( 'subtotal' ) ,
                'total'                    => $group->sum( 'total' ) ,
                'delivery'                 => $group->sum( 'delivery' ) ,
                'status'                   => $first->status ,
                'type'                     => $first->type ,
                'distribution_status'      => $first->distribution_status ,
                'created_at'               => $first->created_at ,
                'updated_at'               => $first->updated_at ,
                'description'              => $first->description ,
                'expiry_date'              => $first->expiry_date ,
                'system_stock'             => $first->system_stock ,
                'physical_stock'           => $first->physical_stock ,
                'difference'               => $first->difference ,
                'discrepancy'              => $first->discrepancy ,
                'classification'           => $first->classification ,
                'product_id'               => $first->product_id ,
                'model_type'               => $first->model_type ,
                'model_id'                 => $first->model_id ,
                'item_type'                => $first->item_type ,
                'item_id'                  => $first->item_id ,
                'sku'                      => $first->sku ,
                'variation_names'          => $first->variation_names ,
                'variation_id'             => $first->variation_id ,
                'unit_id'                  => $first->unit_id ,
                'rate'                     => $first->rate ,
                'purchase_quantity'        => $first->purchase_quantity ,
                'fractional_quantity'      => $first->fractional_quantity ,
                'other_quantity'           => $first->other_quantity ,
                'sold'                     => $group->sum( 'sold' ) ,
                'returned'                 => $group->sum( 'returned' ) ,
                'creator'                  => $first->creator ,
                'user_id'                  => $first->user_id ,
                'driver'                   => $first->driver ?? NULL ,
                'number_plate'             => $first->number_plate ?? NULL ,
            ];
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
