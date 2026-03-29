<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentMethodEnum;
    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\PreOrderStatus;
    use App\Enums\PriceType;
    use App\Enums\RefundStatus;
    use App\Enums\ReturnStatus;
    use App\Enums\SaleOrderType;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Requests\OrderReturnRequest;
    use App\Http\Requests\OrderStatusRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PaymentStatusRequest;
    use App\Http\Requests\PosOrderRequest;
    use App\Http\Resources\OrderResource;
    use App\Libraries\AppLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\Ingredient;
    use App\Models\Order;
    use App\Models\OrderProduct;
    use App\Models\PaymentMethod;
    use App\Models\PaymentMethodTransaction;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\RetailPrice;
    use App\Models\Stock;
    use App\Models\StockTax;
    use App\Models\User;
    use App\Models\WholeSalePrice;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
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
        public function list(Request $request)
        {
            try {
                $orderColumn    = $request->input( 'order_column' ) ?? 'id';
                $orderBy        = $request->input( 'order_by' ) ?? 'desc';
                $page           = $request->input( 'page' ) ?? 1;
                $perPage        = $request->input( 'perPage' ) ?? 10;
                $status         = $request->integer( 'status' );
                $payment_status = $request->integer( 'payment_status' );
                $order_type     = $request->integer( 'order_type' );
                $query          = $request->input( 'query' );
                $start          = $request->date( 'start' );
                $end            = $request->date( 'end' );
                $report         = $request->string( 'report' );
                $exclude        = $request->integer( 'exclude' );
                $query          = $query ? trim( $query ) : NULL;
                $type           = $request->integer( 'type' ) ?? PaymentType::CASH->value;

                $orders                   = Order::with( [
                    'orderProducts.item' => function ($query) {
                        $query->withTrashed();
                    } ,
                    'user' , 'creator' , 'paymentMethods.paymentMethod' , 'originalOrder'
                ] )
                                                 ->when( $query , function (Builder $q) use ($query) {
                                                     $q->where( 'order_serial_no' , 'ilike' , "%$query%" )
                                                       ->orWhere( 'id' , 'ilike' , "%$query%" )
                                                       ->orWhereHas( 'user' , function ($q) use ($query) {
                                                           $q->where( 'name' , 'ilike' , "%$query%" );
                                                       } );
                                                 } )
                                                 ->when( ( $payment_status && $payment_status > 0 ) , function (Builder $q) use ($payment_status) {
                                                     $q->where( 'payment_status' , $payment_status );
                                                 } )
                                                 ->when( ( $status && $status > 0 ) , function (Builder $q) use ($status) {
                                                     $q->where( 'status' , $status );
                                                 } )
                                                 ->when( ( $report == 'sales' ) , function (Builder $q) {
                                                     $q->where( 'status' , '<>' , OrderStatus::CANCELED );
                                                 } )
                                                 ->when( ( $start && ! $end ) , function (Builder $q) use ($start) {
                                                     $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
                                                 } )
                                                 ->when( ( $start && $end ) , function (Builder $q) use ($start , $end) {
                                                     $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
                                                 } )
                                                 ->when( ( $order_type && $order_type > 0 ) , function (Builder $q) use ($order_type) {
                                                     $q->where( 'order_type' , $order_type );
                                                 } )
                                                 ->when( $type , function (Builder $q) use ($type) {
                                                     $q->where( 'payment_type' , $type );
                                                 } )
                                                 ->when( $exclude , function (Builder $q) use ($type) {
                                                     $q->whereIn( 'payment_type' , [ $type ] );
                                                 } );
                $baseQuery                = clone $orders;
                $totalSales               = $baseQuery->sum( 'total' );
                $totalPendingReturnOrders = ( clone $baseQuery )
                    ->where( 'return_status' , ReturnStatus::PENDING->value )
                    ->sum( 'total' );
                $totalPendingRefundOrders = ( clone $baseQuery )
                    ->where( 'refund_status' , RefundStatus::REFUNDED->value )
                    ->sum( 'total' );

                return OrderResource::collection( $orders->orderBy( $orderColumn , $orderBy )
                                                         ->paginate( $perPage , [ '*' ] , 'page' , $page ) )
                                    ->additional(
                                        [
                                            'meta' => [
                                                'totalSales'                  => currency( $totalSales ) ,
                                                'total_pending_return_orders' => currency( $totalPendingReturnOrders ) ,
                                                'total_refund_orders'         => currency( $totalPendingRefundOrders ) ,
                                            ]
                                        ] );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listPerProduct(Request $request)
        {
            try {
                $start   = $request->date( 'start' );
                $end     = $request->date( 'end' );
                $query   = $request->get( 'query' );
                $page    = $request->get( 'page' ) ?? 1;
                $perPage = $request->get( 'perPage' ) ?? 10;

                $products = OrderProduct::query()
                                        ->select( 'item_id' , 'item_type' )
                                        ->selectRaw( 'SUM(quantity) as total_sold' )
                                        ->selectRaw( 'SUM(total) as total_revenue' )
                                        ->whereHas( 'order' , function ($q) use ($start , $end) {
                                            $q->whereIn( 'payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
                                              ->when( ( $start && ! $end ) , function (Builder $q) use ($start) {
                                                  $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
                                              } )
                                              ->when( ( $start && $end ) , function (Builder $q) use ($start , $end) {
                                                  $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
                                              } );
                                        } )
                                        ->when( $query , function ($q) use ($query) {
                                            $q->whereHasMorph( 'item' , [ Product::class , ProductVariation::class ] , function ($q) use ($query) {
                                                $q->where( 'name' , 'ilike' , "%$query%" );
                                            } );
                                        } )
                                        ->groupBy( 'item_id' , 'item_type' )
                                        ->orderByDesc( 'total_revenue' )
                                        ->paginate( $perPage , [ '*' ] , 'page' , $page );

                return $products->through( function ($row) {
                    $item = $row->item;
                    return [
                        'name'          => $item->name ?? 'Unknown Product' ,
                        'category'      => $item->category?->name ?? 'General' ,
                        'quantity_sold' => (int) $row->total_sold ,
                        'total_revenue' => AppLibrary::currencyAmountFormat( $row->total_revenue ) ,
                    ];
                } );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listPerCustomer(Request $request)
        {
            try {
                $start   = $request->date( 'start' );
                $end     = $request->date( 'end' );
                $query   = $request->get( 'query' );
                $page    = $request->get( 'page' ) ?? 1;
                $perPage = $request->get( 'perPage' ) ?? 10;

                $customers = Order::query()
                                  ->select( 'user_id' )
                                  ->selectRaw( 'COUNT(id) as total_orders' )
                                  ->selectRaw( 'SUM(total) as total_revenue' )
                                  ->whereIn( 'payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
                                  ->when( ( $start && ! $end ) , function (Builder $q) use ($start) {
                                      $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
                                  } )
                                  ->when( ( $start && $end ) , function (Builder $q) use ($start , $end) {
                                      $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
                                  } )
                                  ->when( $query , function ($q) use ($query) {
                                      $q->whereHas( 'user' , function ($q) use ($query) {
                                          $q->where( 'name' , 'ilike' , "%$query%" );
                                      } );
                                  } )
                                  ->groupBy( 'user_id' )
                                  ->orderByDesc( 'total_revenue' )
                                  ->paginate( $perPage , [ '*' ] , 'page' , $page );

                return $customers->through( function ($row) {
                    return [
                        'name'          => $row->user->name ?? 'Unknown Customer' ,
                        'total_orders'  => (int) $row->total_orders ,
                        'total_revenue' => AppLibrary::currencyAmountFormat( $row->total_revenue ) ,
                    ];
                } );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function listPerCategory(Request $request)
        {
            try {
                $start   = $request->date( 'start' );
                $end     = $request->date( 'end' );
                $query   = $request->get( 'query' );
                $page    = $request->get( 'page' ) ?? 1;
                $perPage = $request->get( 'perPage' ) ?? 10;

//                $categories = OrderProduct::query()
//                                          ->selectRaw( 'products.product_category_id as category_id' )
//                                          ->selectRaw( 'SUM(order_products.quantity) as total_sold' )
//                                          ->selectRaw( 'SUM(order_products.total) as total_revenue' )
//                                          ->join( 'products' , function ($join) {
//                                              $join->on( 'order_products.item_id' , '=' , 'products.id' )
//                                                   ->where( 'order_products.item_type' , '=' , Product::class );
//                                              // Handle variations if needed by joining product_variations then products
//                                          } )
//                                          ->whereHas( 'order' , function ($q) use ($start , $end) {
//                                              $q->whereIn( 'payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
//                                                ->when( ( $start && ! $end ) , function (Builder $q) use ($start) {
//                                                    $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
//                                                } )
//                                                ->when( ( $start && $end ) , function (Builder $q) use ($start , $end) {
//                                                    $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
//                                                } );
//                                          } )
//                                          ->when( $query , function ($q) use ($query) {
//                                              $q->whereHas( 'product.category' , function ($q) use ($query) {
//                                                  $q->where( 'name' , 'ilike' , "%$query%" );
//                                              } );
//                                          } )
//                                          ->groupBy( 'products.product_category_id' )
//                                          ->with( 'product.category' )
//                                          ->orderByDesc( 'total_revenue' )
//                                          ->paginate( $perPage , [ '*' ] , 'page' , $page );


                $categories = DB::table( 'order_products' )
                                ->join( 'orders' , 'order_products.order_id' , '=' , 'orders.id' )
                                ->leftJoin( 'products' , function ($join) {
                                    $join->on( 'order_products.item_id' , '=' , 'products.id' )
                                         ->where( 'order_products.item_type' , '=' , Product::class );
                                } )
                                ->leftJoin( 'product_variations' , function ($join) {
                                    $join->on( 'order_products.item_id' , '=' , 'product_variations.id' )
                                         ->where( 'order_products.item_type' , '=' , ProductVariation::class );
                                } )
                                ->leftJoin( 'products as parent_products' , 'product_variations.product_id' , '=' , 'parent_products.id' )
                                ->join( 'product_categories' , function ($join) {
                                    $join->on( 'products.product_category_id' , '=' , 'product_categories.id' )
                                         ->orOn( 'parent_products.product_category_id' , '=' , 'product_categories.id' );
                                } )
                                ->select(
                                    'product_categories.name as category_name' ,
                                    DB::raw( 'SUM(order_products.quantity) as total_sold' ) ,
                                    DB::raw( 'SUM(order_products.total) as total_revenue' )
                                )
                                ->whereIn( 'orders.payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
                                ->when( ( $start && ! $end ) , function ($q) use ($start) {
                                    $q->whereBetween( 'orders.created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
                                } )
                                ->when( ( $start && $end ) , function ($q) use ($start , $end) {
                                    $q->whereBetween( 'orders.created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
                                } )
                                ->when( $query , function ($q) use ($query) {
                                    $q->where( 'product_categories.name' , 'ilike' , "%$query%" );
                                } )
                                ->groupBy( 'product_categories.id' , 'product_categories.name' )
                                ->orderByDesc( 'total_revenue' )
                                ->paginate( $perPage , [ '*' ] , 'page' , $page );

                return $categories->through( function ($row) {
                    return [
                        'category'      => $row->category_name ?? 'Uncategorized' ,
                        'quantity_sold' => (int) $row->total_sold ,
                        'total_revenue' => AppLibrary::currencyAmountFormat( $row->total_revenue ) ,
                    ];
                } );

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

                return Order::where( function ($query) use ($requests) {
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
        public function posOrderStore(PosOrderRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $status           = $request->integer( 'status' );
                    $is_preorder      = $request->integer( 'is_preorder' );
                    $paymentType      = $request->integer( 'paymentType' );
                    $customer_id      = $request->integer( 'customer_id' );
                    $change           = $request->change;
                    $delivery_address = $request->delivery_address;
                    $delivery_fee     = $request->delivery_fee;

                    $user = User::find( $customer_id );

                    $paymentStatus = match ( $status ) {
                        SaleOrderType::COMPLETED->value => PaymentStatus::PAID ,
                        default                         => PaymentStatus::UNPAID
                    };

                    if ( $status == SaleOrderType::DEPOSIT->value ) {
                        $paymentStatus = PaymentStatus::PARTIALLY_PAID;
                    }

                    $order = Order::create(
                        $request->validated() + [
                            'paid'            => $request->received ?? 0 ,
                            'balance'         => 0 ,
                            'shipping_charge' => $request->shipping_charge ?? 0 ,
                            'user_id'         => $request->customer_id ,
                            'original_type'   => PaymentMethodEnum::TAKE_AWAY ,
                            'due_date'        => now()->addDays( 30 ) ,
                            'status'          => $status == SaleOrderType::COMPLETED->value ? OrderStatus::COMPLETED->value : OrderStatus::ACCEPT->value ,
                            'change'          => $request->change ,
                            'payment_type'    => $paymentType ,
                            'channel'         => $request->channel ,
                            'creator_id'      => auth()->id() ,
                            'creator_type'    => User::class ,
                            'payment_status'  => $paymentStatus->value ,
                            'warehouse_id'    => $request->warehouse_id ,
                            'order_datetime'  => now() ,
                            'register_id'     => register()->id
                        ]
                    );


                    if ( $is_preorder ) {
                        $order->update( [ 'pre_order_status' => PreOrderStatus::PENDING_STOCK ] );
                    }
                    if ( $order->paid >= $order->total ) $order->update( [ 'payment_status' => PaymentStatus::PAID ] );

                    $this->order = $order;
                    if ( $delivery_address ) {
                        $this->order->delivery_address = $delivery_address;
                    }
                    if ( $delivery_fee ) {
                        $this->order->delivery_fee = $delivery_fee;
                    }
                    $this->order->order_serial_no = orderSerialNo( $this->order );
                    $this->order->save();
                    activity()->log( 'Created order: ' . $order->order_serial_no );
                    $payments = json_decode( $request->payments , TRUE );
                    $ledger   = NULL;
                    if ( $paymentType == PaymentType::CREDIT->value || $paymentType == PaymentType::DEPOSIT->value ) {
                        $ledger = addToLedger( user: $user , reference: 'Items Purchased' , bill_amount: $order->total , paid: 0 );
                    }

                    foreach ( $payments as $p ) {
                        $amount     = $p[ 'amount' ];
                        $net_amount = $amount - $change;
                        if ( $amount > 0 ) {
                            $payment = PaymentMethod::find( $p[ 'id' ] );
                            $ledger?->update( [ 'paid' => $net_amount , 'balance' => $user->credits - $net_amount ] );
                            addPayment( $order , $net_amount , $payment->id , $p[ 'reference' ] );
                        }
//                        else {
//                            $ledger?->update( [ 'paid' => 0 , 'balance' => $user->credits + $order->total ] );
//                        }
                    }

                    $products = json_decode( $request->items , TRUE );

                    if ( ! blank( $products ) ) {
                        foreach ( $products as $product ) {
                            $p = Product::find( $product[ 'item_id' ] );

                            $is_variation = isset( $product[ 'variation_id' ] );
                            $variation    = NULL;
                            $targetModel  = $p;
                            $targetClass  = Product::class;
                            $itemId       = $product[ 'item_id' ];

                            if ( $is_variation ) {
                                $variation_id = $product[ 'variation_id' ];

                                $variation = ProductVariation::find( $variation_id );

                                if ( $variation ) {
                                    $targetModel = $variation;
                                    $targetClass = ProductVariation::class;
                                    $itemId      = $variation->id;
                                }
                            }

                            // Check stock
                            if ( $targetModel->stock < $product[ 'quantity' ] && ! $is_preorder ) {
                                $name = $is_variation ? $p->name . ' (' . $variation?->productAttributeOption?->name . ')' : $p->name;
                                throw  new Exception( "{$name} stock not enough" );
                            }

                            $order_product = OrderProduct::create( [
                                'order_id'                    => $this->order->id ,
                                'item_id'                     => $itemId ,
                                'item_type'                   => $targetClass ,
                                'quantity_picked'             => 0 ,
                                'quantity'                    => $product[ 'quantity' ] ,
                                'price_id'                    => $product[ 'price_id' ] ,
                                'price_type'                  => ( $product[ 'price_type' ] == PriceType::WHOLESALE->value ) ? WholeSalePrice::class :
                                    RetailPrice::class ,
                                'total'                       => $product[ 'quantity' ] * $product[ 'unitPrice' ] ,
                                'unit_price'                  => $product[ 'unitPrice' ] ,
                                'product_attribute_id'        => $product[ 'attribute_id' ] ?? NULL ,
                                'product_attribute_option_id' => $product[ 'option_id' ] ?? NULL ,
                            ] );

                            if ( $is_variation ) $order_product->update( [ 'variation_id' => $variation_id ] );

                            $stock = Stock::where( [
                                'item_id'      => $itemId ,
                                'item_type'    => $targetClass ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $request->warehouse_id
                            ] )->first();

                            $qtyToDecrement = $product[ 'quantity' ];
                            if ( ! $is_preorder ) $stock->decrement( 'quantity' , $qtyToDecrement );

                            if ( $status == SaleOrderType::DEPOSIT->value ) {
                                $stock->increment( 'quantity_ordered' , $qtyToDecrement );
                            }
                        }
                    }
                    $this->order->save();

                } );

                return $this->order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function returnOrderStore(OrderReturnRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $originalOrder   = Order::findOrFail( $request->orderId );
                    $returnItems     = json_decode( $request->returnItems , TRUE ) ?? [];
                    $exchangeItems   = json_decode( $request->exchangeItems , TRUE ) ?? [];
                    $reason          = $request->input( 'reason' );
                    $paymentMethodId = $request->refundMethod;

                    $originalOrder->update( [ 'is_returned' => TRUE ] );

                    $totalReturnValue = collect( $returnItems )->sum( function ($item) {
                        return $item[ 'qty' ] * $item[ 'price' ];
                    } );

                    $totalExchangeValue = collect( $exchangeItems )->sum( function ($item) {
                        return $item[ 'qty' ] * $item[ 'price' ];
                    } );

                    $balance = $totalReturnValue - $totalExchangeValue;

                    $order = new Order( [
                        'user_id'           => $originalOrder->user_id ,
                        'total'             => $balance ,
                        'paid'              => $originalOrder->paid ,
                        'subtotal'          => $originalOrder->subtotal ,
                        'balance'           => $balance ,
                        'refund_status'     => RefundStatus::PENDING ,
                        'return_status'     => ReturnStatus::PENDING ,
                        'status'            => OrderStatus::COMPLETED ,
                        'payment_status'    => PaymentStatus::PAID ,
                        'payment_type'      => PaymentType::RETURN ,
                        'order_type'        => $originalOrder->order_type ,
                        'warehouse_id'      => $originalOrder->warehouse_id ,
                        'creator_id'        => auth()->id() ,
                        'creator_type'      => User::class ,
                        'order_datetime'    => now() ,
                        'reason'            => $reason ,
                        'payment_method'    => $paymentMethodId ,
                        'register_id'       => register()->id ,
                        'original_order_id' => $originalOrder->id ,
                    ] );

                    $order->save();

                    foreach ( $originalOrder->orderProducts as $order_product ) {
                        $return_item = collect( $returnItems )->firstWhere( 'id' , $order_product->id );
                        if ( $return_item ) {
                            $return_item_quantity  = $return_item[ 'qty' ];
                            $return_item_condition = $return_item[ 'condition' ];
                            $price                 = $return_item[ 'price' ];
                            $quantity              = $order_product->quantity - $return_item_quantity;

                            OrderProduct::create( [
                                'order_id'        => $order->id ,
                                'is_return'       => TRUE ,
                                'return_type'     => $return_item_condition ,
                                'item_id'         => $order_product->item_id ,
                                'item_type'       => $order_product->item_type ,
                                'quantity'        => $quantity ,
                                'return_quantity' => $return_item_quantity ,
                                'unit_price'      => $price ,
                                'total'           => ( $return_item_quantity * $price ) ,
                                'price_id'        => $order_product->price_id ,
                                'price_type'      => $order_product->price_type ,
                            ] );
//                            $stock = Stock::where( [
//                                'item_id'      => $order_product->item_id ,
//                                'item_type'    => $order_product->item_type ,
//                                'warehouse_id' => $order->warehouse_id ,
//                                'status'       => StockStatus::RECEIVED
//                            ] )->first();
//
//                            if ( $stock && $return_item_condition == ReturnType::RESELLABLE->value ) {
//                                $stock->increment( 'quantity' , $return_item_quantity );
//                            }

//                            if ( $return_item_condition == ReturnType::DAMAGED->value ) {
//                                $damage = Damage::create( [
//                                    'date'         => now() ,
//                                    'reference_no' => 'D-' . time() ,
//                                    'subtotal'     => 0 ,
//                                    'creator_id'   => auth()->id() ,
//                                    'tax'          => 0 ,
//                                    'discount'     => 0 ,
//                                    'total'        => 0 ,
//                                    'note'         => '' ,
//                                    'reason'       => $reason
//                                ] );
//
//                                $damage->update( [ 'reference_no' => 'D-' . Str::padLeft( $damage->id , Pad::LENGTH , '0' ) ] );
//
//                                Stock::create( [
//                                    'model_type'      => Damage::class ,
//                                    'model_id'        => $damage->id ,
//                                    'warehouse_id'    => $originalOrder->warehouse_id ,
//                                    'item_type'       => $order_product->item_type ,
//                                    'product_id'      => $order_product->id ,
//                                    'variation_names' => 'variation_names' ,
//                                    'item_id'         => $order_product->id ,
//                                    'price'           => 0 ,
//                                    'quantity'        => -$return_item_quantity ,
//                                    'discount'        => 0 ,
//                                    'tax'             => 0 ,
//                                    'subtotal'        => 0 ,
//                                    'total'           => 0 ,
//                                    'sku'             => 'sku' ,
//                                    'status'          => StockStatus::RECEIVED
//                                ] );
//                            }
                        }
                        else {
                            OrderProduct::create( [
                                'order_id'    => $order->id ,
                                'is_return'   => FALSE ,
                                'is_exchange' => FALSE ,
                                'item_id'     => $order_product->item_id ,
                                'item_type'   => $order_product->item_type ,
                                'quantity'    => $order_product->quantity ,
                                'unit_price'  => $order_product->unit_price ,
                                'total'       => $order_product->total ,
                                'price_id'    => $order_product->price_id ,
                                'price_type'  => $order_product->price_type ,
                            ] );
                        }
                    }

                    foreach ( $exchangeItems as $item ) {
                        $product    = Product::find( $item[ 'product_id' ] );
                        $price_id   = (int) $item[ 'price_id' ];
                        $price_type = (int) $item[ 'price_type' ];
                        if ( ! $product ) continue;
                        $targetModel = $product;
                        $targetClass = Product::class;
                        $itemId      = $item[ 'product_id' ];

                        $is_variation = isset( $item[ 'variation_id' ] ) && $item[ 'variation_id' ];

                        if ( $is_variation ) {
                            $variation_id = $item[ 'variation_id' ];
                            $variation    = ProductVariation::find( $variation_id );
                            if ( $variation ) {
                                $targetModel = $variation;
                                $targetClass = ProductVariation::class;
                                $itemId      = $variation->id;
                            }
                        }

                        if ( $targetModel->stock < $item[ 'qty' ] ) {
                            $name = $is_variation ? $product->name . ' (' . $variation?->name . ')' : $product->name;
                            throw new Exception( "{$name} stock not enough for exchange." );
                        }
//
                        OrderProduct::create( [
                            'order_id'    => $order->id ,
                            'is_exchange' => TRUE ,
                            'item_id'     => $itemId ,
                            'item_type'   => $targetClass ,
                            'price_id'    => $price_id ,
                            'price_type'  => ( $price_type == PriceType::RETAIL->value ) ? RetailPrice::class : WholeSalePrice::class ,
                            'quantity'    => $item[ 'qty' ] ,
                            'unit_price'  => $item[ 'price' ] ,
                            'total'       => $item[ 'qty' ] * $item[ 'price' ] ,
                        ] );
//
//                        // Deduct Physical Stock
//                        $stock = Stock::where( [
//                            'item_id'      => $itemId ,
//                            'item_type'    => $targetClass ,
//                            'warehouse_id' => $order->warehouse_id ,
//                            'status'       => StockStatus::RECEIVED
//                        ] )->first();
//
//                        $stock?->decrement( 'quantity' , $item[ 'qty' ] );
//                    }

//                    if ( $balance > 0 ) {
//                        $paymentMethodId = $request->refundMethod;
//
//                        PosPayment::create( [
//                            'order_id'          => $order->id ,
//                            'date'              => now() ,
//                            'reference_no'      => time() ,
//                            'amount'            => -$balance ,
//                            'payment_method_id' => $paymentMethodId ,
//                            'register_id'       => register()->id
//                        ] );
//
//                        PaymentMethodTransaction::create( [
//                            'amount'            => $balance ,
//                            'item_type'         => Order::class ,
//                            'item_id'           => $order->id ,
//                            'charge'            => 0 ,
//                            'description'       => 'Order Return/Exchange #' . $originalOrder->order_serial_no ,
//                            'payment_method_id' => $paymentMethodId ,
//                        ] );
//
//                        $order->paid = $balance;
                    }
                    $order->total = $order->orderProducts()->where( 'is_return' , TRUE )->sum( 'total' );
                    $order->save();
                    $this->order = $order;
                } );

                return $this->order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::error( 'Return Order Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function posOrderUpdate(Order $order , PosOrderRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request , $order) {
                    $status           = $request->integer( 'status' );
                    $change           = $request->change;
                    $delivery_address = $request->delivery_address;
                    $delivery_fee     = $request->delivery_fee;
                    $is_preorder      = $request->integer( 'is_preorder' );

                    $paymentStatus = match ( $status ) {
                        SaleOrderType::COMPLETED->value => PaymentStatus::PAID ,
                        default                         => PaymentStatus::UNPAID
                    };

                    if ( $status == SaleOrderType::DEPOSIT->value ) {
                        $paymentStatus = PaymentStatus::PARTIALLY_PAID;
                    }

                    $order->update(
                        $request->validated() + [
                            'paid'            => $request->received ?? 0 ,
                            'balance'         => 0 ,
                            'shipping_charge' => $request->shipping_charge ?? 0 ,
                            'user_id'         => $request->customer_id ,
                            'status'          => $status == SaleOrderType::COMPLETED->value ? OrderStatus::COMPLETED->value : OrderStatus::ACCEPT->value ,
                            'change'          => $request->change ,
                            'payment_type'    => $request->paymentType ,
                            'channel'         => $request->channel ,
                            'creator_id'      => auth()->id() ,
                            'payment_status'  => $paymentStatus->value ,
                            'warehouse_id'    => $request->warehouse_id ,
                            'register_id'     => register()->id
                        ]
                    );

                    if ( $order->paid >= $order->total ) $order->update( [ 'payment_status' => PaymentStatus::PAID ] );

                    if ( $delivery_address ) {
                        $order->delivery_address = $delivery_address;
                    }
                    if ( $delivery_fee ) {
                        $order->delivery_fee = $delivery_fee;
                    }
                    $order->save();

                    // Restore stock from old order products
                    foreach ( $order->orderProducts as $oldProduct ) {
                        $stock = Stock::where( [
                            'item_id'      => $oldProduct->item_id ,
                            'item_type'    => $oldProduct->item_type ,
                            'status'       => StockStatus::RECEIVED ,
                            'warehouse_id' => $order->warehouse_id
                        ] )->first();

                        if ( ! $order->pre_order_status ) { // If it wasn't a pre-order, restore physical stock
                            $stock?->increment( 'quantity' , $oldProduct->quantity );
                        }
                    }
                    // For simplicity in update, we often delete old items and recreate new ones.
                    $order->orderProducts()->delete();
                    $order->posPayments()->delete();
                    $order->paymentMethodTransactions()->delete();

                    $payments = json_decode( $request->payments , TRUE );
                    foreach ( $payments as $p ) {
                        $amount     = $p[ 'amount' ];
                        $net_amount = $amount - $change;
                        if ( $amount > 0 ) {
                            $payment = PaymentMethod::find( $p[ 'id' ] );

                            PosPayment::create( [
                                'order_id'          => $order->id ,
                                'date'              => now() ,
                                'reference_no'      => $p[ 'reference' ] ?? time() ,
                                'amount'            => $net_amount ,
                                'payment_method_id' => $p[ 'id' ] ,
                                'register_id'       => register()->id
                            ] );

                            PaymentMethodTransaction::create( [
                                'amount'            => $net_amount ,
                                'item_type'         => Order::class ,
                                'item_id'           => $order->id ,
                                'charge'            => 0 ,
                                'description'       => 'Order Payment #' . $order->order_serial_no ,
                                'payment_method_id' => $payment->id ,
                            ] );
                        }
                    }

                    $products = json_decode( $request->items , TRUE );
                    if ( ! blank( $products ) ) {
                        foreach ( $products as $product ) {
                            $p = Product::find( $product[ 'item_id' ] );

                            $is_variation = isset( $product[ 'variation_id' ] );
                            $variation    = NULL;
                            $targetModel  = $p;
                            $targetClass  = Product::class;
                            $itemId       = $product[ 'item_id' ];

                            if ( $is_variation ) {
                                $variation_id = $product[ 'variation_id' ];
                                $variation    = ProductVariation::find( $variation_id );
                                if ( $variation ) {
                                    $targetModel = $variation;
                                    $targetClass = ProductVariation::class;
                                    $itemId      = $variation->id;
                                }
                            }

                            // Check stock
                            if ( $targetModel->stock < $product[ 'quantity' ] && ! $is_preorder ) {
                                $name = $is_variation ? $p->name . ' (' . $variation?->productAttributeOption?->name . ')' : $p->name;
                                throw  new Exception( "{$name} stock not enough" );
                            }

                            $order_product = OrderProduct::create( [
                                'order_id'                    => $order->id ,
                                'item_id'                     => $itemId ,
                                'item_type'                   => $targetClass ,
                                'quantity_picked'             => 0 ,
                                'quantity'                    => $product[ 'quantity' ] ,
                                'price_id'                    => $product[ 'price_id' ] ,
                                'price_type'                  => ( $product[ 'price_type' ] == PriceType::WHOLESALE->value ) ? WholeSalePrice::class : RetailPrice::class ,
                                'total'                       => $product[ 'quantity' ] * $product[ 'unitPrice' ] ,
                                'unit_price'                  => $product[ 'unitPrice' ] ,
                                'product_attribute_id'        => $product[ 'attribute_id' ] ?? NULL ,
                                'product_attribute_option_id' => $product[ 'option_id' ] ?? NULL ,
                            ] );

                            if ( $is_variation ) $order_product->update( [ 'variation_id' => $variation_id ] );

                            $stock = Stock::where( [
                                'item_id'      => $itemId ,
                                'item_type'    => $targetClass ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $request->warehouse_id
                            ] )->first();

                            $qtyToDecrement = $product[ 'quantity' ];
                            if ( ! $is_preorder ) $stock->decrement( 'quantity' , $qtyToDecrement );

                            if ( $status == SaleOrderType::DEPOSIT->value ) {
                                $stock->increment( 'quantity_ordered' , $qtyToDecrement );
                            }
                        }
                    }
                    $this->order = $order;
                } );
                activityLog( "Updated Order: {$order->id}" );
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
                        $order->payment_status = $request->payment_status;
                        $order->save();
                        return $order;
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
                    // Delete related PosPayments
                    if ( $order->posPayments ) {
                        foreach ( $order->posPayments as $posPayment ) {
                            PaymentMethodTransaction::where( 'description' , 'Order Payment #' . $order->order_serial_no )->delete();
                        }
                        $order->posPayments()->delete();
                    }

                    // Delete CreditDepositPurchase records
                    CreditDepositPurchase::where( 'order_id' , $order->id )->delete();

                    // Delete Stocks (and StockTaxes via cascade or manual)
                    if ( $order->stocks ) {
                        $stockIds = $order->stocks->pluck( 'id' );
                        if ( ! blank( $stockIds ) ) {
                            StockTax::whereIn( 'stock_id' , $stockIds )->delete();
                        }
                        $order->stocks()->delete();
                    }

                    // Delete OrderProducts
                    if ( $order->orderProducts ) {
                        $order->orderProducts()->delete();
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

        public function updateStatus(Order $order , Request $request) : object
        {
            try {
                return DB::transaction( function () use ($order , $request) {
                    $order->load( 'orderProducts' );
                    $status = $request->integer( 'status' );

                    if ( $status == OrderStatus::CANCELED->value ) {
//                        $order->posPayments()->delete();
//                        $order->orderProducts()->delete();
                        foreach ( $order->orderProducts as $orderProduct ) {
                            $itemType = ( str_contains( $orderProduct->item_type , 'ProductVariation' ) )
                                ? ProductVariation::class
                                : Product::class;

                            $stock = Stock::where( [
                                'item_id'      => $orderProduct->item_id ,
                                'item_type'    => $itemType ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id
                            ] )->first();
                            $stock?->increment( 'quantity' , $orderProduct->quantity );
                        }
                    }
                    if ( $order->payment_type == PaymentType::PREORDER ) {
                        $order->update( [ 'pre_order_status' => $status ] );
                    }
                    else {
                        $order->update( [ 'status' => $status ] );
                    }
                    activityLog( "Cancelled Order: {$order->order_serial_no}" );
                    return response()->json();
                } );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function fulfillPreOrder(Order $order , array $data) : void
        {
            DB::transaction( function () use ($order , $data) {
                $action = $data[ 'action' ];

                if ( $action === 'TOP_UP' ) {
                    $topUpAmount       = $data[ 'topUpAmount' ];
                    $paymentMethodName = $data[ 'paymentMethod' ];

                    $paymentMethod = PaymentMethod::where( 'name' , $paymentMethodName )->first();
                    if ( ! $paymentMethod ) {
                        // Fallback or throw error. Assuming 'Cash' exists or create one?
                        // For now, let's assume it exists or use ID 1 (Cash usually)
                        $paymentMethod = PaymentMethod::firstOrCreate( [ 'name' => $paymentMethodName ] , [ 'slug' => \Illuminate\Support\Str::slug( $paymentMethodName ) , 'status' => Status::ACTIVE ] );
                    }

                    PosPayment::create( [
                        'order_id'          => $order->id ,
                        'date'              => now() ,
                        'reference_no'      => time() ,
                        'amount'            => $topUpAmount ,
                        'payment_method_id' => $paymentMethod->id ,
                        'register_id'       => register()->id
                    ] );

                    PaymentMethodTransaction::create( [
                        'amount'            => $topUpAmount ,
                        'item_type'         => Order::class ,
                        'item_id'           => $order->id ,
                        'charge'            => 0 ,
                        'description'       => 'Pre-order Top-up #' . $order->order_serial_no ,
                        'payment_method_id' => $paymentMethod->id ,
                    ] );

                    $order->increment( 'paid' , $topUpAmount );
                    $order->increment( 'total' , $topUpAmount ); // Increase total to match the new price
                }
                elseif ( $action === 'PRORATE' ) {
                    $proratedItems = $data[ 'proratedItems' ];
                    foreach ( $proratedItems as $item ) {
                        $orderProduct = OrderProduct::find( $item[ 'orderProductId' ] );
                        if ( $orderProduct && $orderProduct->order_id === $order->id ) {
                            $oldQty = $orderProduct->quantity;
                            $newQty = $item[ 'newQty' ];
                            $diff   = $oldQty - $newQty;

                            if ( $diff > 0 ) {
                                // Release reserved stock for the difference
                                $stock = Stock::where( [
                                    'item_id'      => $orderProduct->item_id ,
                                    'item_type'    => $orderProduct->item_type ,
                                    'status'       => StockStatus::RECEIVED ,
                                    'warehouse_id' => $order->warehouse_id
                                ] )->first();

                                // Since it was a pre-order, stock was reserved (quantity_ordered incremented)
                                // We need to decrement quantity_ordered by the difference
                                // And since we are fulfilling, we will decrement quantity later for the fulfilled amount.
                                // Actually, wait.
                                // When pre-order is created: quantity_ordered += qty. quantity is NOT decremented.
                                // When fulfilled: quantity -= qty. quantity_ordered -= qty.

                                // Here we are reducing the order quantity. So we should reduce quantity_ordered by the difference.
                                $stock?->decrement( 'quantity_ordered' , $diff );

                                $orderProduct->update( [ 'quantity' => $newQty , 'total' => $newQty * $orderProduct->unit_price ] );
                            }
                        }
                    }
                    // Recalculate order total?
                    // The original payment covers the new quantity at the NEW price (effectively).
                    // But the order record has 'total' based on OLD price * OLD qty.
                    // If we prorate, we are essentially saying: "Keep the total amount paid, but reduce quantity so that (new_qty * new_price) ~= paid_amount".
                    // So the order 'total' might need adjustment or stay same?
                    // If we change quantity in order_product, the calculated total of the order will drop (since unit_price is old).
                    // This might be confusing.
                    // However, for inventory purposes, we just need to deduct the correct amount.
                }

                // Finalize fulfillment
                // 1. Deduct stock for the final quantities
                foreach ( $order->orderProducts as $orderProduct ) {
                    $stock = Stock::where( [
                        'item_id'      => $orderProduct->item_id ,
                        'item_type'    => $orderProduct->item_type ,
                        'status'       => StockStatus::RECEIVED ,
                        'warehouse_id' => $order->warehouse_id
                    ] )->first();

                    if ( $stock ) {
                        // Deduct physical stock
                        $stock->decrement( 'quantity' , $orderProduct->quantity );
                        // Clear the reservation
                        $stock->decrement( 'quantity_ordered' , $orderProduct->quantity );
                    }
                }

                $order->update( [
                    'pre_order_status' => PreOrderStatus::FULFILLED ,
                    'status'           => OrderStatus::COMPLETED , // Or DELIVERED
                    'payment_status'   => PaymentStatus::PAID
                ] );
            } );
        }
    }
