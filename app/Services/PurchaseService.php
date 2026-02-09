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
    use App\Models\StockProduct;
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

        public function storeStock3(PurchaseRequest $request)
        {
            try {
                $warehouse_id = $request->input( 'warehouse_id' );
                $products     = json_decode( $request->string( 'products' ) , TRUE );
                $batch        = 'B' . time();

                $stock = Stock::create( [
                    'model_type'   => Product::class ,
                    'model_id'     => 1 ,
                    'warehouse_id' => $warehouse_id ,
                    'reference'    => 'S' . time() ,
                    'item_type'    => Product::class ,
                    'product_id'   => 1 ,
                    'item_id'      => 1 ,
                    'price'        => 0 ,
                    'quantity'     => 0 ,
                    'discount'     => 0 ,
                    'tax'          => 0 ,
                    'batch'        => $batch ,
                    'subtotal'     => 0 ,
                    'total'        => 0 ,
                    'sku'          => 'sku' ,
                    'status'       => StockStatus::RECEIVED
                ] );
                foreach ( $products as $p ) {
                    $product = Product::find( $p[ 'product_id' ] );
                    $total   = $p[ 'quantity' ] * $product->buying_price;
                    StockProduct::create( [
                        'item_type'   => Product::class ,
                        'item_id'     => $product->id ,
                        'stock_id'    => $stock->id ,
                        'quantity'    => $p[ 'quantity' ] ,
                        'weight'      => $p[ 'weight' ] ,
                        'serial'      => $p[ 'serial' ] ,
                        'expiry_date' => $p[ 'expiry' ] ,
                        'subtotal'    => $total ,
                        'total'       => $total ,
                        'unit_id'     => $product->unit_id ,
                    ] );

                }
                return response()->json( [] );
            } catch ( Exception $e ) {
                throw new Exception( $e->getMessage() , 422 );
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
                    Stock::create( [
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
