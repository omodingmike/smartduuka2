<?php

    namespace App\Services;


    use App\Enums\Ask;
    use App\Enums\OrderStatus;
    use App\Enums\OrderType;
    use App\Enums\PaymentMethodEnum;
    use App\Enums\PaymentStatus;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Requests\OrderStatusRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PaymentStatusRequest;
    use App\Http\Requests\PosOrderRequest;
    use App\Libraries\AppLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\Ingredient;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use App\Models\StockTax;
    use App\Models\Unit;
    use App\Models\User;
    use App\Models\Warehouse;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class OrderService
    {
        public object   $order;
        public array    $items;
        public int      $stock;
        protected array $orderFilter = [
            'order_serial_no' ,
            'user_id' ,
            'total' ,
            'order_datetime' ,
            'payment_method' ,
            'payment_status' ,
            'status' ,
            'active' ,
            'source'
        ];

        protected array $exceptFilter = [
            'excepts'
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
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::with( 'orderProducts' )
                            ->whereIn( 'order_type' , [ 10 , OrderType::POS ] )
                            ->when( ( $requests[ 'from_date' ] && $requests[ 'to_date' ] ) , function ($query) use ($requests) {
                                $first_date = Date( 'Y-m-d' , strtotime( $requests[ 'from_date' ] ) );
                                $last_date  = Date( 'Y-m-d' , strtotime( $requests[ 'to_date' ] ) );
                                $query->whereBetween( 'order_datetime' , [ $first_date , $last_date ] );
                            } )
                            ->where( function ($query) use ($requests) {
                                foreach ( $requests as $key => $request ) {
                                    if ( in_array( $key , $this->orderFilter ) ) {
                                        $query->where( $key , 'like' , '%' . $request . '%' );
                                    }
                                }
                            } )->orderBy( $orderColumn , $orderType )->$method(
                        $methodValue
                    );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listCredits(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::with( [ 'orderProducts' ] )
                            ->where( 'order_type' , PaymentMethodEnum::CREDIT )
                            ->where( function ($query) use ($requests) {
                                if ( isset( $requests[ 'from_date' ] ) && isset( $requests[ 'to_date' ] ) ) {
                                    $first_date = Date( 'Y-m-d' , strtotime( $requests[ 'from_date' ] ) );
                                    $last_date  = Date( 'Y-m-d' , strtotime( $requests[ 'to_date' ] ) );
                                    $query->whereDate( 'order_datetime' , '>=' , $first_date )->whereDate(
                                        'order_datetime' ,
                                        '<=' ,
                                        $last_date
                                    );
                                }
                            } )->orderBy( $orderColumn , $orderType )->$method(
                        $methodValue
                    );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listQuotations(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::with( [ 'orderProducts' ] )
                            ->where( function ($query) {
                                $query->where( 'order_type' , PaymentMethodEnum::QUOTATION );
                                $query->orWhere( 'original_type' , PaymentMethodEnum::QUOTATION );
                            } )->where( function ($query) use ($requests) {
                        if ( isset( $requests[ 'from_date' ] ) && isset( $requests[ 'to_date' ] ) ) {
                            $first_date = Date( 'Y-m-d' , strtotime( $requests[ 'from_date' ] ) );
                            $last_date  = Date( 'Y-m-d' , strtotime( $requests[ 'to_date' ] ) );
                            $query->whereDate( 'order_datetime' , '>=' , $first_date )->whereDate(
                                'order_datetime' ,
                                '<=' ,
                                $last_date
                            );
                        }
                    } )->orderBy( $orderColumn , $orderType )->$method(
                        $methodValue
                    );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listDeposits(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::with( 'orderProducts' )
                            ->where( 'order_type' , 25 )
                            ->where( function ($query) use ($requests) {
                                if ( isset( $requests[ 'from_date' ] ) && isset( $requests[ 'to_date' ] ) ) {
                                    $first_date = Date( 'Y-m-d' , strtotime( $requests[ 'from_date' ] ) );
                                    $last_date  = Date( 'Y-m-d' , strtotime( $requests[ 'to_date' ] ) );
                                    $query->whereDate( 'order_datetime' , '>=' , $first_date )->whereDate(
                                        'order_datetime' ,
                                        '<=' ,
                                        $last_date
                                    );
                                }
                            } )->orderBy( $orderColumn , $orderType )->$method(
                        $methodValue
                    );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function myOrder(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::where( 'order_type' , "!=" , OrderType::POS )->where( function ($query) use ($requests) {
                    $query->where( 'user_id' , auth()->user()->id );
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->orderFilter ) ) {
                            $query->where( $key , 'like' , '%' . $request . '%' );
                        }
                        if ( in_array( $key , $this->exceptFilter ) ) {
                            $explodes = explode( '|' , $request );
                            if ( is_array( $explodes ) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where( 'status' , '!=' , $explode );
                                }
                            }
                        }
                    }

                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function userOrder(PaginateRequest $request , User $user)
        {
            try {
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                return Order::where( function ($query) use ($user) {
                    $query->where( 'user_id' , $user->id );
                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function posOrderStore(PosOrderRequest $request , CommissionCalculator $commissionCalculator) : object
        {
            try {
                DB::transaction( function () use ($request , $commissionCalculator) {
                    $order = Order::create(
                        $request->validated() + [
                            'paid'           => $request->initial_amount ?? 0 ,
                            'user_id'        => $request->customer_id ,
                            'original_type'  => $request->order_type == PaymentMethodEnum::QUOTATION ? OrderType::QUOTATION : NULL ,
                            'due_date'       => $request->order_type == PaymentMethodEnum::QUOTATION ? now()->addDays( 30 ) : NULL ,
                            'status'         => $request->order_type == PaymentMethodEnum::QUOTATION ? OrderStatus::PENDING : OrderStatus::CONFIRMED ,
                            'payment_status' => $request->initial_amount ? PaymentStatus::PARTIALLY_PAID : PaymentStatus::UNPAID ,
                            'order_datetime' => $request->date ?? date( 'Y-m-d H:i:s' )
                        ]
                    );

                    $this->order = $order;
                    $user        = Auth::user();

                    $products = json_decode( $request->products );
                    if ( ! blank( $products ) ) {
                        foreach ( $products as $product ) {
                            if ( $request->order_type != PaymentMethodEnum::QUOTATION ) {
                                if ( $product->variation_id ) {
                                    $available_stock = ProductVariation::withSum( 'stockItems' , 'quantity' )
                                                                       ->where( [ 'id' => $product->variation_id ] )->first()?->stock_items_sum_quantity ?? 0;
                                }
                                else {
                                    $available_stock = Product::withSum( 'stockItems' , 'quantity' )
                                                              ->where( [ 'id' => $product->product_id ] )->first()?->stock_items_sum_quantity ?? 0;
                                }
                                if ( $available_stock < $product->quantity ) {
                                    throw new Exception( "Product $product->name out of stock" );
                                }
                            }

                            $order_product = Product::find( $product->product_id );
                            $base_unit     = Unit::find( $order_product->unit_id );
                            $selling_unit  = $product->sellingUnit ?? $product->unit_id ?? $base_unit?->id;
                            $mid_unit_id   = $order_product->mid_unit_id;
                            $top_unit_id   = $order_product->top_unit_id;

                            if ( $order_product->base_units_per_top_unit && $order_product->mid_units_per_top_unit ) {
                                $quantity = match ( $selling_unit ) {
                                    $mid_unit_id => ( $product->quantity * $order_product->units_per_mid_unit ) ,
                                    $top_unit_id => ( $product->quantity * $order_product->base_units_per_top_unit ) ,
                                    default      => $product->quantity
                                };
                            }
                            else {
                                $quantity = $product->quantity;
                            }

                            $stock = Stock::create( [
                                'product_id'          => $product->product_id ,
                                'unit_id'             => $product->sellingUnit ?? $product->unit_id ?? $base_unit->id ,
                                'other_quantity'      => $product->quantity ,
                                'purchase_quantity'   => $product->quantity ,
                                'model_type'          => Order::class ,
                                'model_id'            => $this->order->id ,
                                'variation_id'        => $product->variation_id ,
                                'item_type'           => $product->variation_id ? ProductVariation::class : Product::class ,
                                'item_id'             => $product->variation_id ?: $product->product_id ,
                                'variation_names'     => $product->variation_names ,
                                'sku'                 => $product->sku ,
                                'price'               => $product->price ,
                                'quantity'            => -$quantity ,
                                'fractional_quantity' => $quantity ,
                                'discount'            => clean_amount( $product->discount ) ,
                                'delivery'            => $product->delivery ?? 0 ,
                                'tax'                 => number_format( $product->total_tax , 2 , '.' , '' ) ,
                                'subtotal'            => $product->subtotal ,
                                'total'               => $product->total ,
                                'status'              => Status::ACTIVE ,
                                'creator'             => $user->id ,
                            ] );

                            $commission = $commissionCalculator->calculateForPosStock( $stock );
                            $user->increment( 'commission' , $commission );

                            if ( isDistributor() ) {
                                $stock->update( [ 'user_id' => $user->id ] );
                            }

                            if ( isset( $product->warehouse_id ) && enabledWarehouse() ) {
                                $stock->warehouse_id = $product->warehouse_id;
                            }
                            else {
                                if ( enabledWarehouse() ) {
                                    $stock->warehouse_id = Warehouse::first()?->id;
                                }
                            }
                            $stock->save();
                            if ( $product->otherQuantity && $product->otherQuantity > 0 ) {
                                $stock->other_quantity = $product->otherQuantity;
                                $stock->save();
                            }
                            $j               = 0;
                            $productTaxArray = [];
                            if ( isset( $product?->taxes ) ) {
                                foreach ( $product->taxes as $tax_item ) {
                                    $productTaxArray[ $j ] = [
                                        'stock_id'   => $stock->id ,
                                        'product_id' => $product->product_id ,
                                        'tax_id'     => $tax_item->id ,
                                        'name'       => $tax_item->name ,
                                        'code'       => $tax_item->code ,
                                        'tax_rate'   => $tax_item->tax_rate ,
                                        'tax_amount' => $tax_item->tax_amount ?? ( $tax_item->tax_rate * $product->price ) ,
                                        'created_at' => now() ,
                                        'updated_at' => now()
                                    ];
                                    $j++;
                                }
                            }

                            StockTax::insert( $productTaxArray );

                            $order          = $this->order;
                            $payment_method = PaymentMethod::find( $request->pos_payment_method );
                            $amount         = $request->initial_amount;
                            if ( $request->order_type != OrderType::QUOTATION && $request->order_type != OrderType::CREDIT ) {
                                PosPayment::create( [
                                    'order_id'       => $order->id ,
                                    'date'           => date( 'Y-m-d H:i:s' , time() ) ,
                                    'reference_no'   => time() ,
                                    'amount'         => $amount ,
                                    'payment_method' => $payment_method->id ,
                                ] );
                                $order->order_type = $request->pos_purchase_method;
                            }
                            if ( $request->payment_method ) {
                                $order->payment_method = $request->payment_method;
                            }
                            $order->change   = $request->change;
                            $checkPosPayment = PosPayment::where( 'order_id' , $order->id )->sum( 'amount' );
                            if ( $checkPosPayment === $order->total ) {
                                $order->payment_status = PaymentStatus::PAID;
                                $order->status         = OrderStatus::COMPLETED;
                            }
                            if ( $checkPosPayment < $order->total ) {
                                $order->payment_status = PaymentStatus::UNPAID;
                            }
                        }
                    }
                    $this->order->shipping_charge = $request->delivery;
                    $this->order->order_serial_no = date( 'dmy' ) . $this->order->id;
                    $this->order->save();

                    if ( in_array( $request->order_type , [ OrderType::CREDIT , OrderType::DEPOSIT ] ) ) {
                        $credit           = new CreditDepositPurchase();
                        $credit->user_id  = $request->customer_id;
                        $credit->order_id = $this->order->id;
                        $credit->paid     = $request->initial_amount ?? 0;
                        if ( $request->order_type == OrderType::CREDIT ) {
                            $credit->balance = $this->order->total - ( $request->initial_amount ?? 0 );
                            $credit->type    = 'credit';
                        }
                        elseif ( $request->order_type == OrderType::DEPOSIT ) {
                            $credit->balance = $this->order->total - ( $request->initial_amount ?? 0 );
                            $credit->type    = 'deposit';
                        }
                        $credit->save();
                    }
                } );
                if ( $request->initial_amount && ( $request->initial_amount >= $this->order->total ) ) {
                    $this->order->payment_status = PaymentStatus::PAID;
                }
                else if ( $request->initial_amount && ( $request->initial_amount < $this->order->total ) ) {
                    $this->order->payment_status = PaymentStatus::PARTIALLY_PAID;
                }
                else {
                    $this->order->payment_status = PaymentStatus::UNPAID;
                }
                $this->order->save();
                return $this->order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function posOrderUpdate(PosOrderRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $order = Order::find( $request->order_id );
                    $order->update( $request->validated() + [
                            'user_id'        => $request->customer_id ,
                            'status'         => $request->order_type == PaymentMethodEnum::QUOTATION ? OrderStatus::PENDING : OrderStatus::CONFIRMED ,
                            'payment_status' => ( $request->order_type == PaymentMethodEnum::CREDIT || $request->order_type == PaymentMethodEnum::DEPOSIT ||
                                $request->order_type == PaymentMethodEnum::QUOTATION ) ?
                                PaymentStatus::UNPAID :
                                PaymentStatus::PAID ,
                            'order_datetime' => $request->date ?? date( 'Y-m-d H:i:s' ) ,
                            ''               => $request->date ?? date( 'Y-m-d H:i:s' )
                        ] );
                    $this->order = $order;
                    $products    = json_decode( $request->products );
                    if ( ! blank( $products ) ) {
                        Stock::where( 'model_id' , $this->order->id )->delete();
                        foreach ( $products as $product ) {
                            $order_product = Product::find( $product->product_id );
                            $base_unit     = Unit::find( $order_product->unit_id );
                            $selling_unit  = $product->sellingUnit ?? $product->unit_id ?? $base_unit?->id;
                            $mid_unit_id   = $order_product->mid_unit_id;
                            $top_unit_id   = $order_product->top_unit_id;

                            if ( $order_product->base_units_per_top_unit && $order_product->mid_units_per_top_unit ) {
                                $quantity = match ( $selling_unit ) {
                                    $mid_unit_id => ( $product->quantity * $order_product->units_per_mid_unit ) ,
                                    $top_unit_id => ( $product->quantity * $order_product->base_units_per_top_unit ) ,
                                    default      => $product->quantity
                                };
                            }
                            else {
                                $quantity = $product->quantity;
                            }
                            Stock::create( [
                                'product_id'          => $product->product_id ,
                                'unit_id'             => $product->sellingUnit ?? $product->unit_id ?? $base_unit->id ,
                                'other_quantity'      => $product->quantity ,
                                'purchase_quantity'   => $product->quantity ,
                                'model_type'          => Order::class ,
                                'model_id'            => $this->order->id ,
                                'item_type'           => $product->variation_id > 0 ? ProductVariation::class : Product::class ,
                                'item_id'             => $product->variation_id > 0 ? $product->variation_id : $product->product_id ,
                                'variation_names'     => $product->variation_names ,
                                'sku'                 => $product->sku ,
                                'price'               => $product->price ,
                                'quantity'            => -$quantity ,
                                'fractional_quantity' => $quantity ,
                                'discount'            => clean_amount( $product->discount ) ,
                                'delivery'            => $product->delivery ?? 0 ,
                                'tax'                 => number_format( $product->total_tax , 2 , '.' , '' ) ,
                                'subtotal'            => $product->subtotal ,
                                'total'               => $product->total ,
                                'status'              => Status::INACTIVE ,
                            ] );
                        }
                    }
                } );
                return $this->order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function availableStock(array $data) : int
        {
            $stocks = Stock::with( [ 'product.sellingUnits:id,code' , 'product.unit:id,code' ] )
                           ->when( isset( $data[ 'warehouse_id' ] ) , function ($query) use ($data) {
                               $query->where( 'warehouse_id' , $data[ 'warehouse_id' ] );
                           } )
                           ->when( isset( $data[ 'variation_names' ] ) , function ($query) use ($data) {
                               $query->where( 'variation_names' , $data[ 'variation_names' ] );
                           } )
                           ->when( isset( $data[ 'product_id' ] ) , function ($query) use ($data) {
                               $query->where( 'product_id' , $data[ 'product_id' ] );
                           } )
                           ->when( isset( $data[ 'is_variation' ] ) , function ($query) use ($data) {
                               $query->where( 'item_type' , $data[ 'is_variation' ] ? ProductVariation::class : Product::class );
                           } )
                           ->where( 'status' , StockStatus::RECEIVED )
                           ->where( function ($query) use ($data) {
                               $query->where( 'model_type' , '<>' , Ingredient::class );
                           } )->get();
            if ( ! blank( $stocks ) ) {
                if ( enabledWarehouse() ) {
                    $stocks->groupBy( function ($item) {
                        return $item->product_id . '-' . $item->warehouse_id;
                    } )->map( function ($group) {
                        $first = $group->first();
                        if ( $first[ 'product' ] ) {
                            $item        = [
                                'stock'       => $first[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 'N/C' : $group->sum( 'quantity' ) ,
                                'other_stock' => $first[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 'N/C' : $group->sum( 'other_quantity' ) ,
                            ];
                            $this->stock = $first[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 0 : $group->sum( 'quantity' );
                            if ( $item[ 'stock' ] > 0 ) {
                                $this->items[] = $item;
                            }
                        }
                    } );
                }
                else {
                    $stocks->groupBy( 'product_id' )?->map( function ($product) {
                        $product->groupBy( 'product_id' )?->map( function ($item) {
                            if ( $item->first()[ 'product' ] ) {
                                $this->items[] = [
                                    'stock'       => $item->first()[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 'N/C' : $item->sum( 'quantity' ) ,
                                    'other_stock' => $item->first()[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 'N/C' : $item->sum( 'other_quantity' ) ,
                                ];
                                $this->stock   = $item->first()[ 'product' ][ 'can_purchasable' ] === Ask::NO ? 'N/C' : $item->sum( 'quantity' );
                            }
                        } );
                    } );
                }
            }
            else {
                $this->stock = 0;
            }
            return $this->stock;
        }

        /**
         * @throws \Throwable
         */
        public function posOrderMakeSale(Request $request , CommissionCalculator $commissionCalculator)
        {
            try {
                return DB::transaction( function () use ($request , $commissionCalculator) {
                    $order = Order::find( $request->order_id );
                    $user  = Auth::user();
                    foreach ( $order->stocks as $stock ) {
                        if ( $stock->variation_names ) {
                            $available_stock = ProductVariation::withSum( 'stockItems' , 'quantity' )
                                                               ->where( [ 'id' => $stock?->variation_id ] )->first()?->stock_items_sum_quantity ?? 0;
                        }
                        else {
                            $available_stock = Product::withSum( 'stockItems' , 'quantity' )
                                                      ->where( [ 'id' => $stock->product_id ] )->first()?->stock_items_sum_quantity ?? 0;
                        }
                        if ( $available_stock < abs( $stock->quantity ) ) {
                            $name = $stock->product->name;
                            throw new Exception( "Product $name out of stock" );
                        }
                    }
                    $order->update( [
                        'status'         => OrderStatus::APPROVED ,
                        'paid'           => 0 ,
                        'payment_status' => PaymentStatus::UNPAID ,
                        'order_type'     => $request->order_type ,
                    ] );
                    $order->stocks()->update( [ 'status' => StockStatus::RECEIVED ] );
                    $credit           = new CreditDepositPurchase();
                    $credit->user_id  = $order->user_id;
                    $credit->order_id = $order->id;
                    $credit->paid     = 0;
                    $credit->balance  = $order->total - 0;
                    $credit->type     = 'credit';
                    $credit->save();

                    foreach ( $order->stocks as $stock ) {
                        $commission = $commissionCalculator->calculateForPosStock( $stock );
                        $user->increment( 'commission' , $commission );

                        if ( isDistributor() ) {
                            $stock->update( [ 'user_id' => $user->id ] );
                        }
                    }

                    return $order->load( [ 'orderProducts.unit' , 'orderProducts.product.taxes.tax' , 'orderProducts.product.unit:id,code' , 'orderProducts.product.sellingUnits:id,code' , 'user.addresses' , 'stocks' ] );
                } );
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function update(PosOrderRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $order = Order::find( $request->id );
                    $order->update( $request->validated() );
                    $this->order = $order;
                    $products    = json_decode( $request->products );
                    if ( ! blank( $products ) ) {
                        foreach ( $products as $product ) {
                            $old_stock = Stock::where( 'product_id' , $product->product_id )->where( 'model_id' , $order->id )->first();
                            if ( $old_stock ) {
                                $old_stock->model_type      = Order::class;
                                $old_stock->model_id        = $this->order->id;
                                $old_stock->item_type       = $product->variation_id > 0 ? ProductVariation::class : Product::class;
                                $old_stock->item_id         = $product->variation_id > 0 ? $product->variation_id : $product->product_id;
                                $old_stock->variation_names = $product->variation_names;
                                $old_stock->sku             = $product->sku;
                                $old_stock->price           = $product->price;
                                $old_stock->quantity        = -$product->quantity;
                                $old_stock->discount        = $product->discount;
                                $old_stock->tax             = number_format( $product->total_tax , config( 'system.currency_decimal_point' ) , '.' , '' );
                                $old_stock->subtotal        = $product->subtotal;
                                $old_stock->total           = $product->total;
                                $old_stock->status          = Status::ACTIVE;
                                $old_stock->save();
                            }

                            if ( $product->taxes ) {
                                foreach ( $product->taxes as $tax ) {
                                    $old_stock_tax = StockTax::where( 'stock_id' , $old_stock->id )->where( 'tax_id' , $tax->id )->first();
                                    if ( $old_stock_tax ) {
                                        $old_stock_tax->stock_id   = $old_stock->id;
                                        $old_stock_tax->product_id = $product->product_id;
                                        $old_stock_tax->name       = $tax->name;
                                        $old_stock_tax->code       = $tax->code;
                                        $old_stock_tax->tax_rate   = $tax->tax_rate;
                                        $old_stock_tax->tax_amount = $tax->tax_amount;
                                        $old_stock_tax->updated_at = now();
                                        $old_stock_tax->save();
                                    }
                                }
                            }
                        }
                    }
                    $save = $this->order->save();

                    if ( $save && ( $request->order_type == 20 || $request->order_type == 25 ) ) {
                        $credit = CreditDepositPurchase::where( 'order_id' , $order->id )->first();
                        if ( $credit ) {
                            $credit->user_id = $request->customer_id;
                            $credit->type    = ( $request->order_type == 20 ) ? 'credit' : 'deposit';
                            $credit->paid    = $request->initial_amount;
                            $credit->balance = $this->order->total - $credit->paid;
                            $credit->save();
                        }
                    }
                } );
                return $this->order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(Order $order , $auth = FALSE) : Order | array
        {
            try {
                if ( $auth ) {
                    if ( $order->user_id == Auth::user()->id ) {
                        return $order->load( [ 'orderProducts.unit' , 'paymentMethod' , 'creditDepositPurchases.paymentMethod' , 'orderProducts.product.taxes.tax' , 'orderProducts.product.unit:id,code' , 'orderProducts.product.sellingUnits:id,code' , 'user.addresses' , 'stocks' ] );
                    }
                    else {
                        return [];
                    }
                }
                else {
                    return $order->load( [ 'orderProducts.unit' , 'paymentMethod' , 'creditDepositPurchases.paymentMethod' , 'orderProducts.product.taxes.tax' , 'orderProducts.product.unit:id,code' , 'orderProducts.product.sellingUnits:id,code' , 'user.addresses' , 'stocks' ] );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function orderDetails(User $user , Order $order) : Order | array
        {
            try {
                if ( $order->user_id == $user->id ) {
                    return $order;
                }
                else {
                    return [];
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changeStatus(Order $order , OrderStatusRequest $request , $auth = FALSE) : Order | array
        {
            try {
                if ( $auth ) {
                    if ( $order->user_id == Auth::user()->id ) {
                        if ( $request->reason ) {
                            $order->reason = $request->reason;
                        }

                        $order->status = $request->status;
                        $order->save();
                    }
                }
                else {
                    if ( $request->status == OrderStatus::REJECTED || $request->status == OrderStatus::CANCELED ) {
                        $request->validate( [
                            'reason' => 'required|max:700' ,
                        ] );

                        if ( $request->reason ) {
                            $order->reason = $request->reason;
                        }
                    }
                    $order->status = $request->status;
                    $order->save();
                }
                return $order;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changePaymentStatus(Order $order , PaymentStatusRequest $request , $auth = FALSE) : Order | array
        {
            try {
                if ( $auth ) {
                    if ( $order->user_id == Auth::user()->id ) {
                        $order->payment_status = $request->payment_status;
                        $order->save();
                        return $order;
                    }
                    else {
                        return [];
                    }
                }
                else {
                    $order->payment_status = $request->payment_status;
                    $order->save();
                    return $order;
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Order $order) : void
        {
            try {
                DB::transaction( function () use ($order) {
                    if ( $order?->orderProducts ) {
                        $stockIds = $order?->orderProducts->pluck( 'id' );
                        if ( ! blank( $stockIds ) ) {
                            StockTax::whereIn( 'stock_id' , $stockIds )->delete();
                        }
                        $order?->orderProducts()->delete();
                    }
                    $order->delete();
                } );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function salesReportOverview(Request $request) : array
        {
            try {
                $requests    = $request->all();
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_by' ) ?? 'desc';

                $orders           = Order::with( 'orderProducts' )->where( function ($query) use ($requests) {
                    if ( isset( $requests[ 'from_date' ] ) && isset( $requests[ 'to_date' ] ) ) {
                        $first_date = Date( 'Y-m-d' , strtotime( $requests[ 'from_date' ] ) );
                        $last_date  = Date( 'Y-m-d' , strtotime( $requests[ 'to_date' ] ) );
                        $query->whereDate( 'order_datetime' , '>=' , $first_date )->whereDate(
                            'order_datetime' ,
                            '<=' ,
                            $last_date
                        );
                    }
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->orderFilter ) ) {
                            if ( $key === 'status' ) {
                                $query->where( $key , (int) $request );
                            }
                            else if ( $key === 'payment_method' ) {
                                $query->where( 'pos_payment_method' , $request );
                            }
                            else if ( $key === 'source' ) {
                                $query->where( $key , $request );
                            }
                            else {
                                $query->where( $key , 'like' , '%' . $request . '%' );
                            }
                        }

                        if ( in_array( $key , $this->exceptFilter ) ) {
                            $explodes = explode( '|' , $request );
                            if ( is_array( $explodes ) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where( 'order_type' , '!=' , $explode );
                                }
                            }
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->get();
                $salesReportArray = [];

                $salesReportArray[ 'total_orders' ]    = $orders->count();
                $salesReportArray[ 'total_earnings' ]  = AppLibrary::currencyAmountFormat( $orders->sum( 'total' ) );
                $salesReportArray[ 'total_discounts' ] = AppLibrary::currencyAmountFormat( $orders->sum( 'discount' ) );

                return $salesReportArray;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
