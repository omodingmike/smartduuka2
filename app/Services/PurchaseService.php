<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\ExpenseType;
    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseStatus;
    use App\Enums\PurchaseType;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Enums\StockType;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PurchasePaymentRequest;
    use App\Http\Requests\PurchaseRequest;
    use App\Http\Requests\StockPurchaseRequestRequest;
    use App\Http\Requests\StockReconcilliationRequest;
    use App\Http\Requests\StockTransferRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\Expense;
    use App\Models\ExpenseCategory;
    use App\Models\Ingredient;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Models\Stock;
    use App\Models\StockPurchaseRequest;
    use App\Models\StockTax;
    use App\Models\Tax;
    use App\Models\Warehouse;
    use Exception;
    use Illuminate\Http\Request;
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

                return Purchase::with( [ 'supplier' , 'creator' , 'stocks.product' , 'purchasePayments' ] )->where( function ($query) use ($requests) {
//                    $query->where( 'type' , PurchaseType::ITEM );
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

        public function listRequest(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return StockPurchaseRequest::with( [ 'stocks.product' ] )->where( function ($query) use ($requests) {
//                    $query->where( 'type' , PurchaseType::ITEM );
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
                        'type'           => PurchaseType::STOCK_PURCHASE ,
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
                                    'type'       => PurchaseType::STOCK_PURCHASE ,
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
                        $checkPurchasePayment = PurchasePayment::where( [ 'purchase_id' => $purchase->id , 'purchase_type' => PurchaseType::STOCK_PURCHASE ] )->sum( 'amount' );
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
                    $query->where( 'type' , PurchaseType::STOCK_PURCHASE );
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
                    $warehouse_id   = Warehouse::first()->id;
                    $status         = $request->integer( 'status' );
                    $this->purchase = Purchase::create( [
                        'supplier_id'    => $request->supplier_id ,
                        'date'           => $request->date ,
                        'reference_no'   => $request->reference_no ,
                        'subtotal'       => $request->subtotal ,
                        'total'          => $request->total ,
                        'notes'          => $request->note ? $request->note : "" ,
                        'status'         => $status ,
                        'shipping'       => $request->shipping ?? 0 ,
                        'payment_status' => PurchasePaymentStatus::PENDING->value ,
                        'warehouse_id'   => $warehouse_id ,
                        'tax'            => 0 ,
                        'discount'       => 0
                    ] );

                    activity()->log( 'Created Purchase with id: ' . $this->purchase->id );

                    if ( $request->items ) {
                        $model_id = $this->purchase->id;
                        $products = json_decode( $request->items , TRUE );

                        foreach ( $products as $product ) {
                            Stock::create( [
                                'model_type'       => Purchase::class ,
                                'reference'        => "S" . time() ,
                                'model_id'         => $model_id ,
                                'expiry_date'      => $product[ 'expiry' ] ?? NULL ,
                                'item_type'        => Product::class ,
                                'product_id'       => $product[ 'product_id' ] ,
                                'item_id'          => $product[ 'product_id' ] ,
                                'variation_names'  => 'variation_names' ,
                                'price'            => $product[ 'price' ] ,
                                'quantity'         => $status == PurchaseStatus::RECEIVED->value ? $product[ 'quantity' ] : 0 ,
                                'quantity_ordered' => $product[ 'quantity' ] ,
                                'discount'         => 0 ,
                                'tax'              => 0 ,
                                'subtotal'         => $product[ 'price' ] ,
                                'total'            => $product[ 'price' ] ,
                                'sku'              => 'sku' ,
                                'warehouse_id'     => $warehouse_id ,
                                'status'           => $status == PurchaseStatus::RECEIVED->value ? StockStatus::RECEIVED->value : StockStatus::IN_TRANSIT->value
                            ] );

                            $productModel = Product::find( $product[ 'product_id' ] );

                            $productModel->update( [
                                'buying_price'  => $product[ 'price' ] ,
                                'selling_price' => $product[ 'retailPrices' ][ 0 ][ 'new_price' ] ,
                            ] );

                            // Update Retail Prices
                            if ( isset( $product[ 'retailPrices' ] ) && ! empty( $product[ 'retailPrices' ] ) ) {
                                $productModel->retailPrices()->delete();
                                foreach ( $product[ 'retailPrices' ] as $retailPrice ) {
                                    $productModel->retailPrices()->create(
                                        [
                                            'buying_price'  => $product[ 'price' ] ,
                                            'selling_price' => $retailPrice[ 'new_price' ] ,
                                            'unit_id'       => $retailPrice[ 'unit_id' ]
                                        ]
                                    );
                                }
                            }

                            // Update Wholesale Prices
                            if ( isset( $product[ 'wholesalePrices' ] ) && ! empty( $product[ 'wholesalePrices' ] ) ) {
                                $productModel->wholesalePrices()->delete();
                                foreach ( $product[ 'wholesalePrices' ] as $wholesalePrice ) {
                                    $productModel->wholesalePrices()->create( [
                                        'minQuantity' => $wholesalePrice[ 'min_quantity' ] ,
                                        'price'       => $wholesalePrice[ 'new_price' ]
                                    ] );
                                }
                            }
                        }
                    }
                } );
                return $this->purchase;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function request(StockPurchaseRequestRequest $request) : array
        {
            try {
                DB::transaction( function () use ($request) {
                    $warehouse_id     = Warehouse::first()->id;
                    $purchase_request = StockPurchaseRequest::create( [
                        'reason'         => $request->reason ,
                        'reference'      => 'PR' . time() ,
                        'requester_name' => $request->requester_name ,
                        'date'           => now() ,
                        'department'     => $request->department ,
                        'priority'       => $request->priority ,
                        'supplier_id'    => $request->supplier_id ,
                    ] );
                    activityLog( 'Added stock Request: ' . $purchase_request->id );

                    if ( $request->items ) {
                        $products = json_decode( $request->items , TRUE );
                        foreach ( $products as $product ) {
                            Stock::create( [
                                'model_type'       => StockPurchaseRequest::class ,
                                'reference'        => "S" . time() ,
                                'model_id'         => $purchase_request->id ,
                                'expiry_date'      => $product[ 'expiry' ] ?? NULL ,
                                'item_type'        => Product::class ,
                                'product_id'       => $product[ 'product_id' ] ,
                                'item_id'          => $product[ 'product_id' ] ,
                                'variation_names'  => 'variation_names' ,
                                'price'            => $product[ 'price' ] ,
                                'quantity'         => 0 ,
                                'quantity_ordered' => $product[ 'quantity' ] ,
                                'discount'         => 0 ,
                                'tax'              => 0 ,
                                'subtotal'         => $product[ 'price' ] ,
                                'total'            => $product[ 'price' ] ,
                                'sku'              => 'sku' ,
                                'warehouse_id'     => $warehouse_id ,
                                'status'           => StockStatus::IN_TRANSIT
                            ] );
                        }
                    }
                } );
                return [];
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function receive(Request $request) : array
        {
            try {
                DB::transaction( function () use ($request) {
                    if ( $request->items ) {
                        $products = json_decode( $request->items , TRUE );

                        foreach ( $products as $product ) {
                            $stock = Stock::find( $product[ 'stock_id' ] );
                            $stock->increment( 'quantity_received' , $product[ 'quantity_received' ] );
                            $stock->increment( 'quantity' , $product[ 'quantity_received' ] );
                            $stock->update( [ 'status' => StockStatus::RECEIVED ] );
                        }
                    }
                } );
                return [];
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
                $ingredient_purchase = Purchase::where( [ 'id' => $purchase->id , 'type' => PurchaseType::STOCK_PURCHASE ] )->first();
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
                        'date'           => $request->date ,
                        'reference_no'   => $request->reference_no ,
                        'amount'         => $request->amount ,
                        'payment_method' => $request->payment_method ,
                        'register_id'    => register()->id
                    ] );

                    activityLog( 'Added Stock Purchase Payment: ' . $purchasePayment->id );

                    $expense_category = ExpenseCategory::firstOrCreate( [ 'name' => 'Expense Category' ] , [
                        'description' => 'description' ,
                        'name'        => 'Expense Category' ,
                        'status'      => Status::ACTIVE
                    ] );

                    Expense::create( [
                        'name'                => 'Stock Purchase payment' ,
                        'amount'              => $request->amount ,
                        'date'                => $request->date ,
                        'expense_category_id' => $expense_category->id ,
                        'reference_no'        => $request->reference_no ,
                        'is_recurring'        => 0 ,
                        'expense_type'        => ExpenseType::SYSTEM_CAPTURED->value ,
                        'recurs'              => 0 ,
                        'repetitions'         => 0 ,
                        'repeats_on'          => NULL ,
                        'paid'                => $request->amount ,
                        'paid_on'             => NULL ,
                        'register_id'         => register()->id
                    ] );

                    if ( $request->file ) {
                        $purchasePayment->addMediaFromRequest( 'file' )->toMediaCollection( 'purchase_payment' );
                    }

                    if ( $purchase->paid == $purchase->total ) {
                        $purchase->payment_status = PurchasePaymentStatus::FULLY_PAID;
                        $purchase->save();
                    }

                    if ( $purchase->paid < $purchase->total ) {
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
                    $product        = Product::find( $p[ 'product_id' ] );
                    $variationId    = $p[ 'variation_id' ] ?? NULL;
                    $variationNames = ''; // Default empty for simple products

                    if ( $variationId ) {
                        // VARIANT PRODUCT LOGIC
                        $targetModel = ProductVariation::find( $variationId );
                        $targetClass = ProductVariation::class;
                        $price       = $targetModel->price ?? $product->buying_price;
                        $sku         = $targetModel->sku ?? $product->sku;

                        // Build human-readable path only if variation_path is provided
                        if ( isset( $p[ 'variation_path' ] ) && ! empty( $p[ 'variation_path' ] ) ) {
                            $names = [];
                            ksort( $p[ 'variation_path' ] );
                            foreach ( $p[ 'variation_path' ] as $optionId ) {
                                $option = \App\Models\ProductAttributeOption::with( 'productAttribute' )->find( $optionId );
                                if ( $option && $option->productAttribute ) {
                                    $names[] = $option->productAttribute->name . ' :: ' . $option->name;
                                }
                            }
                            $variationNames = implode( ' > ' , $names ); // e.g., "Color :: Red > Size :: XL"
                        }
                    }
                    else {
                        // SIMPLE PRODUCT LOGIC (Identical to previous version)
                        $targetModel = $product;
                        $targetClass = Product::class;
                        $price       = $product->buying_price;
                        $sku         = $product->sku;
                    }

                    $total = $p[ 'quantity' ] * $price;

                    Stock::create( [
                        'model_type'      => $targetClass ,
                        'model_id'        => $targetModel->id ,
                        'warehouse_id'    => $warehouse_id ,
                        'reference'       => 'S' . time() ,
                        'item_type'       => $targetClass ,
                        'item_id'         => $targetModel->id ,
                        'product_id'      => $product->id ,
                        'variation_id'    => $variationId ,
                        'variation_names' => $variationNames ,
                        'price'           => $price ,
                        'quantity'        => $p[ 'quantity' ] ,
                        // These fields are now correctly mapped from your restored UI
//                        'weight'          => $p[ 'weight' ] ?? NULL ,
//                        'serial'          => $p[ 'serial' ] ?? NULL ,
                        'expiry_date'     => $p[ 'expiry' ] ?? NULL ,
                        'discount'        => 0 ,
                        'tax'             => 0 ,
                        'batch'           => $batch ,
                        'subtotal'        => $total ,
                        'total'           => $total ,
                        'sku'             => $sku ,
                        'status'          => StockStatus::RECEIVED
                    ] );
                }
                return response()->json( [ 'message' => 'Stock stored successfully' ] );
            } catch ( Exception $e ) {
                Log::error( 'Store Stock Error: ' . $e->getMessage() );
                throw new Exception( $e->getMessage() , 422 );
            }
        }

        public function storeStock1(PurchaseRequest $request)
        {
//            return 1;
            try {
                $warehouse_id = $request->input( 'warehouse_id' );
                $products     = json_decode( $request->string( 'products' ) , TRUE );
                $batch        = 'B' . time();
                //  '[{"product_id":3,"selected_attribute_type":1,"quantity":"1000","weight":"","serial":"","variation":{"1":1}}]'
                // '[{"product_id":4,"quantity":"1000","weight":"","serial":"","variation":{}}]'
                foreach ( $products as $p ) {
                    $is_variation = isset( $p[ 'selected_attribute_type' ] );
                    $product      = Product::find( $p[ 'product_id' ] );

                    $product_attribute_id        = $p[ 'product_attribute_id' ] ?? NULL;
                    $product_attribute_option_id = $p[ 'product_attribute_option_id' ] ?? NULL;

                    if ( $is_variation && isset( $p[ 'variation' ] ) ) {
                        // The variation key is like {"1": 1} where key is attribute_id and value is option_id
                        $variationId      = NULL;
                        $variationOptions = $p[ 'variation' ];

                        // Find variation that has exactly these options
                        $optionIds = array_values( $variationOptions );

                        // Use recursive relationship or direct query to find the variation
                        // Assuming ProductVariation has a relationship to ProductAttributeOption via a pivot table or similar
                        // Since ProductVariation model doesn't have productAttributeOptions relationship defined in the provided file,
                        // we need to rely on how variations are structured.
                        // The model has 'product_attribute_id' and 'product_attribute_option_id'.
                        // This suggests a variation is linked to a SINGLE option?
                        // Or is it a recursive structure (parent_id)?

                        // If it's recursive (HasRecursiveRelationships trait used), a variation might be a leaf node in a tree.
                        // A leaf node variation would have a path of options.

                        // Given the complexity and missing relationship in the model file provided earlier,
                        // let's try to find the variation by matching the specific option ID if it's a single level,
                        // or iterate if we can't query directly.

                        // However, the payload {"1": 1} suggests multiple attributes could be present.
                        // If ProductVariation represents a single combination (SKU), it should be findable.

                        // Let's try to find a variation that matches the last option in the chain if it's hierarchical,
                        // or matches the set of options if it's flat.

                        // Since I can't call productAttributeOptions(), I will try to find by the option ID directly if possible,
                        // assuming the variation record itself holds the option ID (product_attribute_option_id).

                        // If the payload has multiple options, we might need to traverse.
                        // But for {"1": 1}, it's just one option.

                        // Let's try to find a variation where product_attribute_option_id matches one of the values.
                        // And product_id matches.

                        // If there are multiple options, this logic needs to be smarter.
                        // But for now, let's assume the provided option ID identifies the variation.

                        $variation = ProductVariation::where( 'product_id' , $product->id )
                                                     ->whereIn( 'product_attribute_option_id' , $optionIds )
                                                     ->first();

                        if ( $variation ) {
                            $targetModel = $variation;
                            $targetClass = ProductVariation::class;
                            $price       = $variation->price ?? $product->buying_price; // Use variation price if available
                            $sku         = $variation->sku ?? $product->sku;
                            $variationId = $variation->id;
                        }
                        else {
                            // Fallback to product if variation not found (should not happen if data is correct)
                            $targetModel = $product;
                            $targetClass = Product::class;
                            $price       = $product->buying_price;
                            $sku         = $product->sku;
                            $variationId = NULL;
                        }
                    }
                    else {
                        $targetModel = $product;
                        $targetClass = Product::class;
                        $price       = $product->buying_price;
                        $sku         = $product->sku;
                        $variationId = NULL;
                    }

                    $total = $p[ 'quantity' ] * $price;

                    Stock::create( [
                        'model_type'                  => $targetClass ,
                        'model_id'                    => $targetModel->id ,
                        'warehouse_id'                => $warehouse_id ,
                        'reference'                   => 'S' . time() ,
                        'item_type'                   => $targetClass ,
                        'item_id'                     => $targetModel->id ,
                        'product_id'                  => $product->id , // Always keep reference to parent product
                        'variation_id'                => $variationId ,
                        'price'                       => $price ,
                        'quantity'                    => $p[ 'quantity' ] ,
                        'discount'                    => 0 ,
                        'tax'                         => 0 ,
                        'batch'                       => $batch ,
                        'subtotal'                    => $total ,
                        'total'                       => $total ,
                        'sku'                         => $sku ,
                        'product_attribute_id'        => $product_attribute_id ,
                        'product_attribute_option_id' => $product_attribute_option_id ,
                        'status'                      => StockStatus::RECEIVED
                    ] );
                }
                return response()->json( [] );
            } catch ( Exception $e ) {
                throw new Exception( $e->getMessage() , 422 );
            }
        }

        public function transferStock(StockTransferRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $products = json_decode( $request->input( 'products' ) , TRUE );
                    $batch    = 'B' . time();
                    $type     = (int) $request->type;
                    $status   = $type === StockType::TRANSFER ? StockStatus::IN_TRANSIT : StockStatus::PENDING;

                    $driver          = $request->driver;
                    $number_plate    = $request->number_plate;
                    $referencePrefix = $type === StockType::TRANSFER ? 'ST' : 'SR';

                    $reference = $referencePrefix . time();
                    foreach ( $products as $p ) {
                        $product     = Product::find( $p[ 'product_id' ] );
                        $total       = $product->buying_price * $p[ 'quantity' ];
                        $this->stock = Stock::create( [
                            'model_type'               => Purchase::class ,
                            'model_id'                 => $product->id ,
                            'batch'                    => $batch ,
                            'type'                     => $type ,
                            'reference'                => $reference ,
                            'quantity'                 => -$p[ 'quantity' ] ,
                            'request_quantity'         => $p[ 'quantity' ] ,
                            'source_warehouse_id'      => $request->source_warehouse_id ,
                            'destination_warehouse_id' => $request->destination_warehouse_id ,
                            'item_type'                => Product::class ,
                            'product_id'               => $product->id ,
                            'item_id'                  => $product->id ,
                            'price'                    => $total ,
                            'discount'                 => 0 ,
                            'tax'                      => 0 ,
                            'subtotal'                 => $total ,
                            'total'                    => $total ,
                            'sku'                      => $product->sku ,
                            'status'                   => $status->value ,
                        ] );
                        if ( $driver && $number_plate ) {
                            $this->stock->update( [ 'driver' => $driver , 'number_plate' => $number_plate ] );
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
//                        $products       = json_decode( $request->products , TRUE );
//                        $this->purchase = Purchase::create( [
//                            'supplier_id'    => $request->supplier_id ?? Supplier::first()->id ,
//                            'supplier_id'    => 0 ,
//                            'date'           => now() ,
//                            'reference_no'   => time() ,
//                            'subtotal'       => 0 ,
//                            'tax'            => 0 ,
//                            'discount'       => 0 ,
//                            'total'          => 0 ,
//                            'type'           => 20 ,
//                            'note'           => $request->notes ? $request->notes : '' ,
//                            'status'         => 15 ,
//                            'payment_status' => PurchasePaymentStatus::FULLY_PAID
//                        ] );
//                        $model_id       = $this->purchase->id;

                        $products = json_decode( $request->products , TRUE );
                        $batch    = "B" . time();
                        $type     = $request->type;
                        foreach ( $products as $product ) {
                            $base_product = Product::find( $product[ 'product_id' ] );
//                            $base_units_per_top_unit = $base_product->base_units_per_top_unit;
                            $stock       = $base_product->stocks->sum( 'quantity' );
                            $difference  = $product[ 'physical_count' ] - $stock;
                            $total       = $base_product->buying_price * $difference;
                            $this->stock = Stock::create( [
                                'model_type'      => Purchase::class ,
                                'model_id'        => 1 ,
                                'creator'         => auth()->id() ,
                                'batch'           => $batch ,
                                'type'            => $type ,
                                'reference'       => ( $type == StockType::TRANSFER ? 'ST' : ( $type == StockType::RECONCILIATION ? 'RST' : 'SR' ) ) . time() ,
                                'warehouse_id'    => $request->warehouse_id ,
                                'description'     => $product[ 'notes' ] ,
//                                'item_type'       => $product[ 'is_variation' ] ? ProductVariation::class : Product::class ,
                                'item_type'       => Product::class ,
                                'product_id'      => $product[ 'product_id' ] ,
                                'item_id'         => $product[ 'product_id' ] ,
                                'system_stock'    => $stock ,
                                'physical_stock'  => $product[ 'physical_count' ] ,
                                'difference'      => $difference ,
                                'unit_id'         => 1 ,
                                'discrepancy'     => 'discrepancy' ,
                                'classification'  => 'classification' ,
                                'variation_names' => 'variation_names' ,
                                'price'           => $base_product->buying_price * $difference ,
                                'quantity'        => $difference ,
                                'discount'        => 0 ,
                                'tax'             => 0 ,
                                'subtotal'        => $total ,
                                'total'           => $total ,
                                'sku'             => $base_product->sku ,
                                'status'          => StockStatus::RECEIVED
                            ] );
                            activityLog( "Added stock Reconciliation for: $base_product->name" );
                        }
                    }
                } );
                return response()->json( [] );
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
