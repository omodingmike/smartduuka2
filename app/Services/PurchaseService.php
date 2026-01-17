<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseStatus;
    use App\Enums\PurchaseType;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Enums\StockType;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PurchasePaymentRequest;
    use App\Http\Requests\PurchaseRequest;
    use App\Http\Requests\StockReconcilliationRequest;
    use App\Http\Requests\StockTransferRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\DistributionRoute;
    use App\Models\Expense;
    use App\Models\ExpenseCategory;
    use App\Models\Ingredient;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Models\Stock;
    use App\Models\StockTax;
    use App\Models\Supplier;
    use App\Models\Tax;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class PurchaseService
    {
        public object   $purchase;
        public object   $stock;
        protected array $purchaseFilter = [
            'supplier_id' ,
            'date' ,
            'reference_no' ,
            'status' ,
            'total' ,
            'note' ,
            'except'
        ];

        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return Purchase::with( 'supplier' )->where( function ($query) use ($requests) {
                    $query->where( 'type' , PurchaseType::ITEM );
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->purchaseFilter ) ) {
                            if ( $key == "except" ) {
                                $explodes = explode( '|' , $request );
                                if ( count( $explodes ) ) {
                                    foreach ( $explodes as $explode ) {
                                        $query->where( 'id' , '!=' , $explode );
                                    }
                                }
                            }
                            else {
                                if ( $key == "supplier_id" || $key == 'status' ) {
                                    $query->where( $key , $request );
                                }
                                else if ( $key == "date" && ! empty( $request ) ) {
                                    $date_start = date( 'Y-m-d 00:00:00' , strtotime( $request ) );
                                    $date_end   = date( 'Y-m-d 23:59:59' , strtotime( $request ) );
                                    $query->where( $key , '>=' , $date_start )->where( $key , '<=' , $date_end );
                                }
                                else {
                                    $query->where( $key , 'like' , '%' . $request . '%' );
                                }
                            }
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method( $methodValue );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function storeIngredient(PurchaseRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $purchase        = Purchase::create( [
                        'supplier_id'    => $request->supplier_id ,
                        'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'reference_no'   => $request->reference_no ,
                        'subtotal'       => $request->subtotal ,
                        'tax'            => $request->tax ,
                        'type'           => PurchaseType::INGREDIENT ,
                        'discount'       => $request->discount ,
                        'balance'        => $request->amount ? $request->amount : $request->total ,
                        'total'          => $request->total ,
                        'note'           => $request->note ? $request->note : '' ,
                        'status'         => $request->status ,
                        'payment_status' => PurchasePaymentStatus::PENDING ,
                    ] );
                    $this->purchase  = $purchase;
                    $purchasePayment = NULL;
                    if ( $request->add_payment == Ask::YES ) {
                        $purchasePayment = PurchasePayment::create( [
                            'purchase_id'    => $purchase->id ,
                            'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->payment_date ) ) ,
                            'reference_no'   => $request->reference_no ,
                            'amount'         => $request->amount ,
                            'payment_method' => $request->payment_method ,
                        ] );
                    }

                    if ( $request->products ) {
                        $products = json_decode( $request->products , TRUE );
                        foreach ( $products as $product ) {
                            $stock = Stock::where( 'model_type' , Ingredient::class )
                                          ->where( 'model_id' , $product[ 'product_id' ] )
                                          ->first();
                            if ( $stock ) {
                                return $stock->increment( 'quantity' , $product[ 'quantity' ] );
                            }
                            else {
                                Stock::create( [
                                    'model_type' => Purchase::class ,
                                    'model_id'   => $purchase->id ,
                                    'item_type'  => Ingredient::class ,
                                    'product_id' => $product[ 'product_id' ] ,
                                    'item_id'    => $product[ 'product_id' ] ,
                                    'price'      => $product[ 'price' ] ,
                                    'type'       => PurchaseType::INGREDIENT ,
                                    'quantity'   => $product[ 'quantity' ] ,
                                    'discount'   => $product[ 'total_discount' ] ,
                                    'tax'        => $product[ 'total_tax' ] ,
                                    'subtotal'   => $product[ 'subtotal' ] ,
                                    'total'      => $product[ 'total' ] ,
                                    'status'     => $request->status == PurchaseStatus::RECEIVED ? Status::ACTIVE : Status::INACTIVE
                                ] );
                            }
                        }
                    }

                    if ( $request->file ) {
                        $this->purchase->addMediaFromRequest( 'file' )->toMediaCollection( 'purchase' );
                    }
                    if ( $request->payment_file ) {
                        $purchasePayment->addMediaFromRequest( 'payment_file' )->toMediaCollection( 'purchase_payment' );
                    }
                    if ( $request->add_payment == Ask::YES ) {
                        $checkPurchasePayment = PurchasePayment::where( [ 'purchase_id' => $purchase->id , 'purchase_type' => PurchaseType::INGREDIENT ] )->sum( 'amount' );
                        if ( $checkPurchasePayment == $purchase->total ) {
                            $purchase->payment_status = PurchasePaymentStatus::FULLY_PAID;
                            $purchase->save();
                        }
                        if ( $checkPurchasePayment < $purchase->total ) {
                            $purchase->payment_status = PurchasePaymentStatus::PARTIAL_PAID;
                            $purchase->save();
                        }
                    }
                } );
                activityLog( 'Purchased Raw materials' );
                return $this->purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function ingreidentList(PaginateRequest $request)
        {

            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return Purchase::with( 'supplier' )->where( function ($query) use ($requests) {
                    $query->where( 'type' , PurchaseType::INGREDIENT );
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->purchaseFilter ) ) {
                            if ( $key == 'except' ) {
                                $explodes = explode( '|' , $request );
                                if ( count( $explodes ) ) {
                                    foreach ( $explodes as $explode ) {
                                        $query->where( 'id' , '!=' , $explode );
                                    }
                                }
                            }
                            else {
                                if ( $key == 'supplier_id' || $key == 'status' ) {
                                    $query->where( $key , $request );
                                }
                                else if ( $key == 'date' && ! empty( $request ) ) {
                                    $date_start = date( 'Y-m-d 00:00:00' , strtotime( $request ) );
                                    $date_end   = date( 'Y-m-d 23:59:59' , strtotime( $request ) );
                                    $query->where( $key , '>=' , $date_start )->where( $key , '<=' , $date_end );
                                }
                                else {
                                    $query->where( $key , 'like' , '%' . $request . '%' );
                                }
                            }
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method( $methodValue );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function store(PurchaseRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $this->purchase = Purchase::create( [
                        'supplier_id'    => $request->supplier_id ,
                        'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'reference_no'   => $request->reference_no ,
                        'subtotal'       => $request->subtotal ,
                        'tax'            => $request->tax ,
                        'discount'       => $request->discount ,
                        'balance'        => $request->amount ? $request->amount : $request->total ,
                        'total'          => $request->total ,
                        'note'           => $request->note ? $request->note : "" ,
                        'status'         => $request->status ,
                        'type'           => $request->purchase_type ,
                        'payment_status' => PurchasePaymentStatus::PENDING
                    ] );

                    if ( $request->warehouse_id ) {
                        $this->purchase->warehouse_id = $request->warehouse_id;
                        $this->purchase->save();
                    }

                    if ( $request->products ) {
                        $model_id = $this->purchase->id;
                        $products = json_decode( $request->products , TRUE );
                        $taxes    = Tax::all()->keyBy( 'id' );

                        foreach ( $products as $product ) {
                            $base_product            = Product::find( $product[ 'product_id' ] );
                            $base_units_per_top_unit = $base_product->base_units_per_top_unit;
                            $stock                   = Stock::create( [
                                'model_type'      => Purchase::class ,
                                'reference'       => "S" . time() ,
                                'model_id'        => $model_id ,
                                'expiry_date'     => isset( $product[ 'expiry' ] ) ? date( 'Y-m-d H:i:s' , strtotime( $product[ 'expiry' ] ) ) : NULL ,
                                'item_type'       => $product[ 'is_variation' ] ? ProductVariation::class : Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'item_id'         => $product[ 'item_id' ] ,
                                'variation_names' => $product[ 'variation_names' ] ,
                                'price'           => $product[ 'price' ] ,
//                                'quantity'        => $product['quantity'] ,
                                'quantity'        => $base_units_per_top_unit ? $product[ 'quantity' ] * $base_units_per_top_unit : $product[ 'quantity' ] ,
                                'discount'        => $product[ 'total_discount' ] ,
                                'tax'             => $product[ 'total_tax' ] ,
                                'subtotal'        => $product[ 'subtotal' ] ,
                                'total'           => $product[ 'total' ] ,
                                'sku'             => $product[ 'sku' ] ,
                                'status'          => $request->status == PurchaseStatus::RECEIVED ? Status::ACTIVE : Status::INACTIVE
                            ] );
                            if ( $request->warehouse_id ) {
                                $stock->warehouse_id = $request->warehouse_id;
                                $stock->save();
                            }

                            if ( isset( $product[ 'tax_id' ] ) && count( $product[ 'tax_id' ] ) > 0 ) {
                                foreach ( $product[ 'tax_id' ] as $tax_id ) {
                                    if ( isset( $taxes[ $tax_id ] ) ) {
                                        $tax = $taxes[ $tax_id ];
                                        StockTax::create( [
                                            'stock_id'   => $stock->id ,
                                            'product_id' => $product[ 'product_id' ] ,
                                            'tax_id'     => $tax->id ,
                                            'name'       => $tax->name ,
                                            'code'       => $tax->code ,
                                            'tax_rate'   => $tax->tax_rate ,
                                            'tax_amount' => ( $tax->tax_rate * ( $product[ 'price' ] * $product[ 'quantity' ] ) ) / 100 ,
                                        ] );
                                    }
                                }
                            }
                        }
                    }

                    if ( $request->file ) {
                        $this->purchase->addMediaFromRequest( 'file' )->toMediaCollection( 'purchase' );
                    }
                } );
                return $this->purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(Purchase $purchase) : Purchase
        {
            try {
                $product_purchase = Purchase::where( [ 'id' => $purchase->id ] )->first();
                return $product_purchase->load( 'media' );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function showIngredient(Purchase $purchase) : Purchase
        {
            try {
                $ingredient_purchase = Purchase::where( [ 'id' => $purchase->id , 'type' => PurchaseType::INGREDIENT ] )->first();
                return $ingredient_purchase->load( 'media' );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function edit(Purchase $purchase) : Purchase
        {
            try {
                return $purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(PurchaseRequest $request , Purchase $purchase) : object
        {
            try {
                DB::transaction( function () use ($request , $purchase) {
                    $purchase->update( [
                        'supplier_id'  => $request->supplier_id ,
                        'date'         => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'reference_no' => $request->reference_no ,
                        'subtotal'     => $request->subtotal ,
                        'tax'          => $request->tax ,
                        'discount'     => $request->discount ,
                        'total'        => $request->total ,
                        'note'         => $request->note ? $request->note : "" ,
                        'status'       => $request->status
                    ] );

                    if ( $request->products ) {
                        $model_id = $purchase->id;
                        $products = json_decode( $request->products , TRUE );
                        if ( $purchase->stocks ) {
                            $stockIds = $purchase->stocks->pluck( 'id' );
                            if ( ! blank( $stockIds ) ) {
                                StockTax::whereIn( 'stock_id' , $stockIds )->delete();
                            }
                            $purchase->stocks()->delete();
                        }
                        $taxes = Tax::all()->keyBy( 'id' );
                        foreach ( $products as $product ) {
                            $stock = Stock::create( [
                                'model_type'      => Purchase::class ,
                                'model_id'        => $model_id ,
                                'item_type'       => $product[ 'is_variation' ] ? ProductVariation::class : Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'variation_names' => $product[ 'variation_names' ] ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'price'           => $product[ 'price' ] ,
                                'quantity'        => $product[ 'quantity' ] ,
                                'discount'        => $product[ 'total_discount' ] ,
                                'tax'             => $product[ 'total_tax' ] ,
                                'subtotal'        => $product[ 'subtotal' ] ,
                                'total'           => $product[ 'total' ] ,
                                'sku'             => $product[ 'sku' ] ,
                                'status'          => $request->status == PurchaseStatus::RECEIVED ? Status::ACTIVE : Status::INACTIVE
                            ] );
                            if ( isset( $product[ 'tax_id' ] ) && count( $product[ 'tax_id' ] ) > 0 ) {
                                foreach ( $product[ 'tax_id' ] as $tax_id ) {
                                    if ( isset( $taxes[ $tax_id ] ) ) {
                                        $tax = $taxes[ $tax_id ];
                                        StockTax::create( [
                                            'stock_id'   => $stock->id ,
                                            'product_id' => $product[ 'product_id' ] ,
                                            'tax_id'     => $tax->id ,
                                            'name'       => $tax->name ,
                                            'code'       => $tax->code ,
                                            'tax_rate'   => $tax->tax_rate ,
                                            'tax_amount' => ( $tax->tax_rate * ( $product[ 'price' ] * $product[ 'quantity' ] ) ) / 100 ,
                                        ] );
                                    }
                                }
                            }
                        }
                    }

                    if ( $request->file ) {
                        $file = $purchase->getFirstMedia( 'purchase' );
                        if ( isset( $file ) ) {
                            $file->delete();
                        }
                        $purchase->addMediaFromRequest( 'file' )->toMediaCollection( 'purchase' );
                    }
                } );
                return $purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Purchase $purchase) : void
        {
            try {
                DB::transaction( function () use ($purchase) {
                    if ( $purchase->stocks ) {
                        $stockIds = $purchase->stocks->pluck( 'id' );
                        if ( ! blank( $stockIds ) ) {
                            StockTax::whereIn( 'stock_id' , $stockIds )->delete();
                        }
                        $purchase->stocks()->delete();
                    }
                    $file = $purchase->getFirstMedia( 'purchase' );
                    $file?->delete();
                    $purchase->delete();
                } );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        public function downloadAttachment(Purchase $purchase)
        {
            return $purchase->getMedia( 'purchase' )->first();
        }

        /**
         * @throws Exception
         */
        public function payment(PurchasePaymentRequest $request , Purchase $purchase) : object
        {
            try {
                DB::transaction( function () use ($request , $purchase) {
                    $purchasePayment = PurchasePayment::create( [
                        'purchase_id'    => $purchase->id ,
                        'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'reference_no'   => $request->reference_no ,
                        'amount'         => $request->amount ,
                        'purchase_type'  => $request->purchase_type ,
                        'payment_method' => $request->payment_method ,
                    ] );

                    Expense::create( [
                        'name'          => 'Raw materials / Item purchases' ,
                        'amount'        => $request->amount ,
                        'date'          => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'category'      => 0 ,
                        'paymentMethod' => $request->payment_method ,
                        'referenceNo'   => $request->reference_no ,
                        'isRecurring'   => 0 ,
                        'recurs'        => 0 ,
                        'user_id'       => auth()->id() ,
                        'repetitions'   => 0 ,
                        'repeats_on'    => NULL ,
                        'paid'          => $request->amount ,
                        'paid_on'       => NULL ,
                    ] );

                    if ( $request->file ) {
                        $purchasePayment->addMediaFromRequest( 'file' )->toMediaCollection( 'purchase_payment' );
                    }

                    $paid = PurchasePayment::where( [ 'purchase_id' => $purchase->id , 'purchase_type' => $request->purchase_type ] )->sum( 'amount' );

                    if ( $paid == $purchase->total ) {
                        $purchase->payment_status = PurchasePaymentStatus::FULLY_PAID;
                        $purchase->save();
                    }

                    if ( $paid < $purchase->total ) {
                        $purchase->payment_status = PurchasePaymentStatus::PARTIAL_PAID;
                        $purchase->save();
                    }
                } );
                return $purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function paymentHistory(int $type , Purchase $purchase) : object
        {
            try {
                return PurchasePayment::where( [ 'purchase_id' => $purchase->id , 'purchase_type' => $type ] )->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function storeStock(PurchaseRequest $request)
        {
            try {
                $warehouse_id = $request->input( 'warehouse_id' );
                $products     = json_decode( $request->string( 'products' ) , TRUE );
                $batch        = 'B' . time();
                foreach ( $products as $p ) {
                    $product = Product::find( $p[ 'product_id' ] );
                    $total   = $p[ 'quantity' ] * $product->buying_price;
                  $stock =  Stock::create( [
                        'model_type'   => Product::class ,
                        'model_id'     => $product->id ,
                        'warehouse_id' => $warehouse_id ,
                        'reference'    => 'S' . time() ,
                        'item_type'    => Product::class ,
                        'product_id'   => $product->id ,
                        'item_id'      => $product->id ,
                        'price'        => $product->buying_price ,
                        'quantity'     => $p[ 'quantity' ] ,
                        'discount'     => 0 ,
                        'tax'          => 0 ,
                        'batch'        => $batch ,
                        'subtotal'     => $total ,
                        'total'        => $total ,
                        'sku'          => $product->sku ,
                        'status'       => StockStatus::RECEIVED
                    ] );
                  info($stock);
                }
                return response()->json( [] );
            } catch ( Exception $e ) {
                throw new Exception( $e->getMessage() , 422 );
            }
        }

        public function storeStock1(PurchaseRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    if ( $request->products ) {
                        $this->purchase = Purchase::create( [
                            'supplier_id'    => $request->supplier_id ?? Supplier::first()->id ,
                            'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                            'reference_no'   => time() ,
                            'subtotal'       => 0 ,
                            'tax'            => 0 ,
                            'discount'       => 0 ,
                            'total'          => 0 ,
                            'type'           => 20 ,
                            'note'           => $request->note ? $request->note : '' ,
                            'status'         => 15 ,
                            'payment_status' => PurchasePaymentStatus::FULLY_PAID
                        ] );
                        $model_id       = $this->purchase->id;
                        $batch          = "B" . time();
                        if ( $request->warehouse_id ) {
                            $this->purchase->warehouse_id = $request->warehouse_id;
                            $this->purchase->save();
                        }
                        $products = json_decode( $request->products , TRUE );
                        $taxes    = Tax::all()->keyBy( 'id' );
                        foreach ( $products as $product ) {
                            $base_product            = Product::find( $product[ 'product_id' ] );
                            $base_units_per_top_unit = $base_product->base_units_per_top_unit;
                            $this->stock             = Stock::create( [
                                'model_type'      => Purchase::class ,
                                'model_id'        => $model_id ,
                                'warehouse_id'    => $request->source_warehouse_id ,
                                'batch'           => $batch ,
                                'reference'       => 'S' . time() ,
                                'expiry_date'     => isset( $product[ 'expiry' ] ) ? date( 'Y-m-d H:i:s' , strtotime( $product[ 'expiry' ] ) ) : NULL ,
                                'item_type'       => $product[ 'is_variation' ] ? ProductVariation::class : Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'item_id'         => $product[ 'item_id' ] ,
                                'variation_names' => $product[ 'variation_names' ] ,
                                'price'           => $base_units_per_top_unit ? $base_product->retail_price_per_base_unit : $product[ 'price' ] ,
                                'quantity'        => $base_units_per_top_unit ? $product[ 'quantity' ] * $base_units_per_top_unit : $product[ 'quantity' ] ,
                                'other_quantity'  => $product[ 'otherQuantity' ] ,
                                'discount'        => $product[ 'total_discount' ] ,
                                'tax'             => $product[ 'total_tax' ] ,
                                'subtotal'        => $product[ 'subtotal' ] ,
                                'total'           => $product[ 'total' ] ,
                                'sku'             => $product[ 'sku' ] ,
                                'status'          => StockStatus::RECEIVED
                            ] );
                            if ( $request->warehouse_id ) {
                                $this->stock->warehouse_id = $request->warehouse_id;
                                $this->stock->save();
                            }

                            if ( isset( $product[ 'tax_id' ] ) && count( $product[ 'tax_id' ] ) > 0 ) {
                                foreach ( $product[ 'tax_id' ] as $tax_id ) {
                                    if ( isset( $taxes[ $tax_id ] ) ) {
                                        $tax = $taxes[ $tax_id ];
                                        StockTax::create( [
                                            'stock_id'   => $this->stock->id ,
                                            'product_id' => $product[ 'product_id' ] ,
                                            'tax_id'     => $tax->id ,
                                            'name'       => $tax->name ,
                                            'code'       => $tax->code ,
                                            'tax_rate'   => $tax->tax_rate ,
                                            'tax_amount' => ( $tax->tax_rate * ( $product[ 'price' ] * $product[ 'quantity' ] ) ) / 100 ,
                                        ] );
                                    }
                                }
                            }
                        }
                    }
                } );
                return $this->stock;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function transferStock(StockTransferRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    if ( $request->products ) {
                        $this->purchase = Purchase::create( [
                            'supplier_id'    => $request->supplier_id ?? Supplier::first()->id ,
                            'date'           => $request->date ? date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) : now() ,
                            'reference_no'   => time() ,
                            'subtotal'       => 0 ,
                            'tax'            => 0 ,
                            'discount'       => 0 ,
                            'total'          => 0 ,
                            'type'           => 20 ,
                            'note'           => $request->note ? $request->note : '' ,
                            'status'         => 15 ,
                            'payment_status' => PurchasePaymentStatus::FULLY_PAID
                        ] );
                        $model_id       = $this->purchase?->id;

                        if ( $request->shipping_charges > 0 ) {
                            $expenseCategory = ExpenseCategory::firstOrCreate( [
                                'name'    => 'Stock fees' ,
                                'user_id' => auth()->id()
                            ] );
                            $expense         = Expense::create( [
                                'name'          => 'Stock Shipping' ,
                                'amount'        => $request->shipping_charges ,
                                'date'          => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                                'category'      => $expenseCategory->id ,
                                'paymentMethod' => 0 ,
                                'referenceNo'   => 'E' . time() ,
                                'isRecurring'   => 0 ,
                                'recurs'        => 0 ,
                                'user_id'       => auth()->id() ,
                                'repetitions'   => 0 ,
                                'repeats_on'    => NULL ,
                                'paid'          => $request->shipping_charges ,
                                'paid_on'       => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                            ] );
                            activityLog( "Created expense: $expense->name" );
                        }

                        if ( $request->destination_warehouse_id ) {
                            $this->purchase->warehouse_id = $request->destination_warehouse_id;
                            $this->purchase->save();
                        }

                        $products        = json_decode( $request->products , TRUE );
                        $batch           = "B" . time();
                        $route_value     = 0;
                        $is_distribution = $request->type == StockType::DISTRIBUTION;
                        foreach ( $products as $product ) {
                            $base_product            = Product::find( $product[ 'product_id' ] );
                            $base_units_per_top_unit = $base_product->base_units_per_top_unit;

                            $referencePrefix = $request->type === 'transfer' ? 'ST' : 'SR';
                            if ( $is_distribution ) {
                                $referencePrefix = 'SD';
                            }
                            $reference = $referencePrefix . time();

                            $commonData = [
                                'model_type'      => Purchase::class ,
                                'model_id'        => $model_id ,
                                'batch'           => $batch ,
                                'reference'       => $reference ,
                                'description'     => $request->description ,
                                'expiry_date'     => isset( $product[ 'expiry' ] )
                                    ? date( 'Y-m-d H:i:s' , strtotime( $product[ 'expiry' ] ) )
                                    : NULL ,
                                'item_type'       => $product[ 'is_variation' ]
                                    ? ProductVariation::class
                                    : Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'item_id'         => $product[ 'item_id' ] ,
                                'variation_names' => $product[ 'variation_names' ] ,
                                'price'           => $product[ 'price' ] ,
                                'other_quantity'  => $product[ 'otherQuantity' ] ,
                                'discount'        => $product[ 'total_discount' ] ,
                                'tax'             => $product[ 'total_tax' ] ,
                                'subtotal'        => $product[ 'subtotal' ] ,
                                'total'           => $product[ 'total' ] ,
                                'sku'             => $product[ 'sku' ] ,
                                'status'          => $is_distribution ? StockStatus::RECEIVED : StockStatus::PENDING ,
                            ];

                            if ( $request->source_warehouse_id ) {
                                $commonData[ 'source_warehouse_id' ] = $request->source_warehouse_id;
                            }

                            if ( $request->destination_warehouse_id ) {
                                $commonData[ 'destination_warehouse_id' ] = $request->destination_warehouse_id;
                            }

                            $quantityBase = $base_units_per_top_unit
                                ? $product[ 'quantity' ] * $base_units_per_top_unit
                                : $product[ 'quantity' ];

                            // Decrease stock (source warehouse)
                            Stock::create( [
                                ...$commonData ,
                                'warehouse_id' => $request->source_warehouse_id ,
                                'quantity'     => -$quantityBase ,
                            ] );

                            if ( $request->user_id ) {
                                $commonData[ 'status' ] = StockStatus::RECEIVED;
                            }
                            // Increase stock (destination warehouse)
                            $this->stock = Stock::create( [
                                ...$commonData ,
                                'user_id'  => $request->user_id ,
                                'quantity' => $quantityBase ,
                                'delivery' => $request->shipping_charges ,
                            ] );

                            $route_value += $quantityBase * $base_product->selling_price;

                            activityLog( "Added stock for: $base_product->name" );
                        }
                        if ( $is_distribution ) {
                            $distribution_route = DistributionRoute::create( [
                                'user_id'     => $request->user_id ,
                                'route_value' => $route_value ,
                                'stock_batch' => $batch ,
                            ] );
                            activityLog( "Added distribution route: $distribution_route->id" );
                        }
                    }
                } );
                return $this->stock;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function reconcileStock(StockReconcilliationRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    if ( $request->products ) {
                        $this->purchase = Purchase::create( [
                            'supplier_id'    => $request->supplier_id ?? Supplier::first()->id ,
                            'date'           => now() ,
                            'reference_no'   => time() ,
                            'subtotal'       => 0 ,
                            'tax'            => 0 ,
                            'discount'       => 0 ,
                            'total'          => 0 ,
                            'type'           => 20 ,
                            'note'           => $request->notes ? $request->notes : '' ,
                            'status'         => 15 ,
                            'payment_status' => PurchasePaymentStatus::FULLY_PAID
                        ] );
                        $model_id       = $this->purchase->id;

                        $products = json_decode( $request->products , TRUE );
                        $batch    = "B" . time();
                        $type     = $request->type;
                        foreach ( $products as $product ) {
                            $base_product            = Product::find( $product[ 'product_id' ] );
                            $base_units_per_top_unit = $base_product->base_units_per_top_unit;
                            $this->stock             = Stock::create( [
                                'model_type'      => Purchase::class ,
                                'model_id'        => $model_id ,
                                'creator'         => auth()->id() ,
                                'batch'           => $batch ,
                                'reference'       => ( $type == 'transfer' ? 'ST' : ( $type == 'reconciliation' ? 'RST' : 'SR' ) ) . time() ,
                                'warehouse_id'    => $request->source_warehouse_id ,
                                'description'     => $product[ 'notes' ] ,
                                'item_type'       => $product[ 'is_variation' ] ? ProductVariation::class : Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'item_id'         => $product[ 'item_id' ] ,
                                'system_stock'    => $product[ 'topStock' ] ,
                                'physical_stock'  => $product[ 'quantity' ] ,
                                'difference'      => $product[ 'difference' ] ,
                                'unit_id'         => $product[ 'unit_id' ] ,
                                'discrepancy'     => $product[ 'discrepancy' ] ,
                                'classification'  => $product[ 'classification' ] ,
                                'variation_names' => $product[ 'variation_names' ] ,
                                'price'           => $product[ 'price' ] ,
                                'quantity'        => $base_units_per_top_unit ? $product[ 'difference' ] * $base_units_per_top_unit : $product[ 'difference' ] ,
                                'discount'        => $product[ 'total_discount' ] ,
                                'tax'             => $product[ 'total_tax' ] ,
                                'subtotal'        => $product[ 'subtotal' ] ,
                                'total'           => $product[ 'total' ] ,
                                'sku'             => $product[ 'sku' ] ,
                                'status'          => StockStatus::RECEIVED
                            ] );
                            activityLog( "Added stock Reconciliation for: $base_product->name" );
                        }
                    }
                } );
                return $this->stock;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function paymentDownloadAttachment(PurchasePayment $purchasePayment)
        {
            return $purchasePayment->getMedia( 'purchase_payment' )->first();
        }

        /**
         * @throws Exception
         */
        public function paymentDestroy(int $type , Purchase $purchase , PurchasePayment $purchasePayment) : void
        {
            try {
                PurchasePayment::where( [ 'purchase_id' => $purchasePayment->id , 'purchase_type' => $type ] )->delete();

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
