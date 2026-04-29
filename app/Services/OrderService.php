<?php

    namespace App\Services;

    use App\Enums\CustomerWalletTransactionType;
    use App\Enums\DefaultPaymentMethods;
    use App\Enums\ItemType;
    use App\Enums\OrderStatus;
    use App\Enums\OrderType;
    use App\Enums\PaymentMethodEnum;
    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\PreOrderStatus;
    use App\Enums\PriceType;
    use App\Enums\QuotationStatus;
    use App\Enums\QuotationType;
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
    use App\Http\Requests\QuotationRequest;
    use App\Http\Resources\OrderResource;
    use App\Jobs\SendWhatsappQuotation;
    use App\Libraries\AppLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\Order;
    use App\Models\OrderProduct;
    use App\Models\OrderServiceAdon;
    use App\Models\OrderServiceProduct;
    use App\Models\OrderServiceTier;
    use App\Models\PaymentMethod;
    use App\Models\PaymentMethodTransaction;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\RetailPrice;
    use App\Models\Service;
    use App\Models\Stock;
    use App\Models\StockTax;
    use App\Models\User;
    use App\Models\Warehouse;
    use App\Models\WholeSalePrice;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    // Added Service model

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

        public function list(Request $request)
        {
            try {
                $orderColumn    = $request->input( 'order_column' ) ?? 'id';
                $orderBy        = $request->input( 'order_by' ) ?? 'desc';
                $page           = $request->input( 'page' ) ?? 1;
                $per_page       = $request->input( 'per_page' ) ?? 10;
                $status         = $request->integer( 'status' );
                $payment_status = $request->integer( 'payment_status' );
                $order_type     = $request->integer( 'order_type' );
                $query          = $request->input( 'query' );
                $start          = $request->date( 'start' );
                $end            = $request->date( 'end' );
                $report         = $request->string( 'report' );
                $exclude        = $request->integer( 'exclude' );
                $query          = $query ? trim( $query ) : NULL;
                $type           = $request->integer( 'type' );

                $orders = Order::select( 'orders.*' )
                               ->with( [
                                   'orderProducts.item' => function ($q) {
                                       $q->withTrashed();
                                   } ,
                                   'user' ,
                                   'creator' ,
                                   'paymentMethods.paymentMethod' ,
                                   'originalOrder' ,
                                   'posPayments'        => fn($q) => $q->latest() ,
                                   'posPayments.paymentMethod' ,
                                   'orderServiceProducts.service' ,
                                   'orderServiceProducts.addons.addon' ,
                                   'orderServiceProducts.tier.serviceTier'
                               ] )
                               ->withSum( 'posPayments as calculated_net_paid' , 'amount' )
                               ->withSum( 'orderProducts as calculated_new_total' , DB::raw( 'unit_price * quantity' ) )
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
                                   if ( $type == PaymentType::CASH->value ) {
                                       $q->where( function (Builder $query) use ($type) {
                                           $query->where( 'payment_type' , $type )
                                                 ->orWhere( 'quotation_status' , QuotationStatus::CONVERTED );
                                       } );
                                   }
                                   else {
                                       $q->where( 'payment_type' , $type );
                                   }
                               } )
                               ->when( ( $exclude && $order_type !== OrderType::QUOTATION->value ) , function (Builder $q) use ($type) {
                                   $q->whereIn( 'payment_type' , [ $type ] );
                               } );

                $baseQuery                = clone $orders;
                $totalSales               = $baseQuery->sum( 'total' );
                $totalPendingReturnOrders = ( clone $baseQuery )->where( 'return_status' , ReturnStatus::PENDING->value )->sum( 'total' );
                $totalPendingRefundOrders = ( clone $baseQuery )->where( 'refund_status' , RefundStatus::REFUNDED->value )->sum( 'total' );

                $quotations     = ( clone $baseQuery )->where( 'order_type' , OrderType::QUOTATION )->get();
                $pending        = $quotations->where( 'quotation_status' , QuotationStatus::PENDING )->count();
                $approved       = $quotations->where( 'quotation_status' , QuotationStatus::CONVERTED )->count();
                $rejected       = $quotations->whereIn( 'quotation_status' , [ QuotationStatus::CANCELLED , QuotationStatus::EXPIRED ] )->count();
                $total          = $quotations->count();
                $conversionRate = $total > 0 ? round( ( $approved / $total ) * 100 ) : 0;

                $paginatedOrders = $orders->orderBy( $orderColumn , $orderBy )
                                          ->paginate( $per_page , [ '*' ] , 'page' , $page );

                $paginatedOrders->getCollection()->transform( function ($order) {
                    $order->calculated_last_paid = $order->posPayments->first();
                    $order->net_paid             = $order->calculated_net_paid ?? 0;
                    $order->calculated_new_total = $order->calculated_new_total ?? 0;
                    return $order;
                } );

                return OrderResource::collection( $paginatedOrders )
                                    ->additional( [
                                        'meta' => [
                                            'totalSales'                  => currency( $totalSales ) ,
                                            'total_pending_return_orders' => currency( $totalPendingReturnOrders ) ,
                                            'total_refund_orders'         => currency( $totalPendingRefundOrders ) ,
                                            'pending'                     => $pending ,
                                            'approved'                    => $approved ,
                                            'rejected'                    => $rejected ,
                                            'conversionRate'              => $conversionRate
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
                $start    = $request->date( 'start' );
                $end      = $request->date( 'end' );
                $query    = $request->get( 'query' );
                $page     = $request->get( 'page' ) ?? 1;
                $per_page = $request->get( 'per_page' ) ?? 10;

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
                                        ->paginate( $per_page , [ '*' ] , 'page' , $page );

                return $products->through( function ($row) use ($start , $end) {
                    $item = $row->item;

                    $breakdowns = OrderProduct::query()
                                              ->select( 'unit_price' )
                                              ->selectRaw( 'SUM(quantity) as quantity_sold' )
                                              ->selectRaw( 'SUM(total) as total_revenue' )
                                              ->where( 'item_id' , $row->item_id )
                                              ->where( 'item_type' , $row->item_type )
                                              ->whereHas( 'order' , function ($q) use ($start , $end) {
                                                  $q->whereIn( 'payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
                                                    ->when( ( $start && ! $end ) , function (Builder $q) use ($start) {
                                                        $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $start->copy()->endOfDay() ] );
                                                    } )
                                                    ->when( ( $start && $end ) , function (Builder $q) use ($start , $end) {
                                                        $q->whereBetween( 'created_at' , [ $start->copy()->startOfDay() , $end->copy()->endOfDay() ] );
                                                    } );
                                              } )
                                              ->groupBy( 'unit_price' )
                                              ->orderByDesc( 'total_revenue' )
                                              ->get();

                    $formattedBreakdowns = $breakdowns->map( function ($b) {
                        return [
                            'price_currency'         => AppLibrary::currencyAmountFormat( $b->unit_price ) ,
                            'quantity_sold'          => (int) $b->quantity_sold ,
                            'total_revenue_currency' => AppLibrary::currencyAmountFormat( $b->total_revenue ) ,
                        ];
                    } );

                    return [
                        'id'               => $row->item_id ,
                        'name'             => $item->name ?? 'Unknown Product' ,
                        'category'         => $item->category?->name ?? 'General' ,
                        'quantity_sold'    => (int) $row->total_sold ,
                        'total_revenue'    => AppLibrary::currencyAmountFormat( $row->total_revenue ) ,
                        'price_breakdowns' => $formattedBreakdowns
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
                $start    = $request->date( 'start' );
                $end      = $request->date( 'end' );
                $query    = $request->get( 'query' );
                $page     = $request->get( 'page' ) ?? 1;
                $per_page = $request->get( 'per_page' ) ?? 10;

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
                                  ->paginate( $per_page , [ '*' ] , 'page' , $page );

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
                $start    = $request->date( 'start' );
                $end      = $request->date( 'end' );
                $query    = $request->get( 'query' );
                $page     = $request->get( 'page' ) ?? 1;
                $per_page = $request->get( 'per_page' ) ?? 10;

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
                                ->paginate( $per_page , [ '*' ] , 'page' , $page );

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
                $method      = $request->integer( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->integer( 'paginate' , 0 ) == 1 ? $request->integer( 'per_page' , 10 ) : '*';
                $orderColumn = $request->string( 'order_column' ) ?? 'id';
                $orderType   = $request->string( 'order_by' ) ?? 'desc';

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

        public function posOrderStore(PosOrderRequest $request) : object
        {
            try {
                DB::transaction( function () use ($request) {
                    $status           = $request->integer( 'status' );
                    $is_preorder      = $request->integer( 'is_preorder' );
                    $paymentType      = $request->integer( 'paymentType' );
                    $customer_id      = $request->integer( 'customer_id' );
                    $delivery_address = $request->delivery_address;
                    $delivery_fee     = $request->delivery_fee;
                    $warehouse_id     = $request->warehouse_id;

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
                            'warehouse_id'    => $warehouse_id ,
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
                        $net_amount = $amount;
                        if ( $amount > 0 ) {
                            $payment = PaymentMethod::find( $p[ 'id' ] );
                            $ledger?->update( [ 'paid' => $net_amount , 'balance' => $user->credits - $net_amount ] );
                            addPayment( $order , $net_amount , $payment->id , $p[ 'reference' ] ?? time() );
                            if ( $payment->name == DefaultPaymentMethods::WALLET->value ) {
                                addToCustomerWalletTransaction(
                                    $user ,
                                    -$net_amount ,
                                    CustomerWalletTransactionType::PURCHASE ,
                                    $payment->id ,
                                    $order->order_serial_no
                                );
                            }
                        }
                    }

                    $items = json_decode( $request->items , TRUE );

                    if ( ! blank( $items ) ) {
                        foreach ( $items as $item ) {
                            if ( ! isset( $item[ 'itemType' ] ) ) {
                                $item[ 'itemType' ] = ItemType::PRODUCT->value;
                            }
                            if ( $item[ 'itemType' ] === ItemType::PRODUCT->value ) {
                                $this->handlePosProductItem( $this->order , $item , $is_preorder , $warehouse_id , $status );
                            }
                            elseif ( $item[ 'itemType' ] === ItemType::SERVICE->value ) {
                                $this->handlePosServiceItem( $this->order , $item , $is_preorder , $warehouse_id , $status );
                            }
                        }
                    }
                    $this->order->save();

                } );

                return $this->order->load( [
                    'orderProducts.item' => function ($q) {
                        $q->withTrashed();
                    } ,
                    'orderProducts.product.taxes.tax' ,
                    'orderProducts.product.unit:id,code' ,
                    'orderProducts.product.sellingUnits:id,code' ,
                    'user.addresses' ,
                    'creator' ,
                    'paymentMethods.paymentMethod' ,
                    'originalOrder' ,
                    'posPayments'        => fn($q) => $q->latest() ,
                    'posPayments.paymentMethod' ,
                    'orderServiceProducts.service' ,
                    'orderServiceProducts.addons.addon' ,
                    'orderServiceProducts.tier.serviceTier' ,
                    'creditDepositPurchases.paymentMethod' ,
                    'stocks'
                ] );
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function quotationUpdate(Order $order , QuotationRequest $request) : JsonResponse
        {
            try {
                DB::transaction( function () use ($order , $request) {
                    $order->update(
                        array_merge( $request->validated() , [
                            'user_id'        => $request->customer_id ,
                            'due_date'       => $request->expiry_date ,
                            'order_datetime' => $request->date ,
                            'reason'         => $request->notes ?? $order->reason ,
                            'warehouse_id'   => $request->warehouse_id ?? $order->warehouse_id ,
                        ] )
                    );

                    $this->order = $order;
                    activity()->log( 'Updated Quotation: ' . $order->order_serial_no );

                    $order->orderProducts()->delete();
                    $order->orderServiceProducts()->delete();

                    $items = json_decode( $request->items , TRUE );

                    if ( ! blank( $items ) ) {
                        $hasProducts = FALSE;
                        $hasServices = FALSE;
                        foreach ( $items as $item ) {
                            if ( $item[ 'type' ] === ItemType::PRODUCT->value ) {
                                $this->handleProductQuotationItem( $this->order , $item );
                                $hasProducts = TRUE;
                            }
                            elseif ( $item[ 'type' ] === ItemType::SERVICE->value ) {
                                $this->handleServiceQuotationItem( $this->order , $item );
                                $hasServices = TRUE;
                            }
                        }

                        if ( $hasProducts && $hasServices ) {
                            $this->order->quotation_type = QuotationType::COMBINED;
                        }
                        elseif ( $hasProducts ) {
                            $this->order->quotation_type = QuotationType::PRODUCT;
                        }
                        elseif ( $hasServices ) {
                            $this->order->quotation_type = QuotationType::SERVICE;
                        }
                    }

                    $this->order->total = $this->order->orderProducts()->sum( 'total' ) + $this->order->orderServiceProducts()->sum( 'total' );
                    $this->order->save();

                } );
                return response()->json( [ 'message' => 'Quotation updated successfully' ] );
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function quotationStore(QuotationRequest $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $order = Order::create(
                        array_merge( $request->validated() , [
                            'paid'             => 0 ,
                            'balance'          => 0 ,
                            'shipping_charge'  => 0 ,
                            'user_id'          => $request->customer_id ,
                            'due_date'         => $request->expiry_date ,
                            'quotation_status' => QuotationStatus::PENDING ,
                            'change'           => 0 ,
                            'status'           => OrderStatus::PENDING ,
                            'payment_type'     => PaymentType::QUOTATION ,
                            'creator_id'       => auth()->id() ,
                            'creator_type'     => User::class ,
                            'payment_status'   => PaymentStatus::UNPAID ,
                            'warehouse_id'     => $request->warehouse_id ?? Warehouse::first()->id ,
                            'order_datetime'   => $request->date ,
                            'reason'           => $request->notes ,
                            'register_id'      => register()?->id
                        ] )
                    );

                    $this->order = $order;

                    $this->order->order_serial_no = orderSerialNo( $this->order );
                    $this->order->save();
                    activity()->log( 'Created Quotation: ' . $order->order_serial_no );

                    $items = json_decode( $request->items , TRUE );

                    if ( ! blank( $items ) ) {
                        $hasProducts = FALSE;
                        $hasServices = FALSE;
                        foreach ( $items as $item ) {
                            if ( $item[ 'type' ] === ItemType::PRODUCT->value ) {
                                $this->handleProductQuotationItem( $this->order , $item );
                                $hasProducts = TRUE;
                            }
                            elseif ( $item[ 'type' ] === ItemType::SERVICE->value ) {
                                $this->handleServiceQuotationItem( $this->order , $item );
                                $hasServices = TRUE;
                            }
                        }

                        if ( $hasProducts && $hasServices ) {
                            $this->order->quotation_type = QuotationType::COMBINED;
                        }
                        elseif ( $hasProducts ) {
                            $this->order->quotation_type = QuotationType::PRODUCT;
                        }
                        elseif ( $hasServices ) {
                            $this->order->quotation_type = QuotationType::SERVICE;
                        }
                    }
                    $this->order->save();
                } );

                return response()->json();
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        private function handlePosProductItem(Order $order , array $product , bool $is_preorder , int $warehouse_id , int $status) : void
        {
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

            if ( $targetModel->stock < $product[ 'quantity' ] && ! $is_preorder ) {
                $name = $is_variation ? $p->name . ' (' . $variation?->productAttributeOption?->name . ')' : $p->name;
                throw new Exception( "{$name} stock not enough" );
            }

            $order_product = OrderProduct::create( [
                'order_id'                    => $order->id ,
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
                'warehouse_id' => $warehouse_id
            ] )->first();

            $qtyToDecrement = $product[ 'quantity' ];
            if ( ! $is_preorder ) $stock->decrement( 'quantity' , $qtyToDecrement );

            if ( $status == SaleOrderType::DEPOSIT->value ) {
                $stock->increment( 'quantity_ordered' , $qtyToDecrement );
            }
        }

        private function handlePosServiceItem(Order $order , array $service , bool $is_preorder , int $warehouse_id , int $status) : void
        {
            $orderServiceProduct = OrderServiceProduct::create( [
                'order_id'   => $order->id ,
                'service_id' => $service[ 'item_id' ] ,
                'quantity'   => $service[ 'quantity' ] ,
                'total'      => $service[ 'quantity' ] * $service[ 'unitPrice' ] ,
                'unit_price' => $service[ 'unitPrice' ]
            ] );

            if ( isset( $service[ 'addons' ] ) ) {
                foreach ( $service[ 'addons' ] as $addon ) {
                    OrderServiceAdon::create( [ 'order_service_product_id' => $orderServiceProduct->id , 'addon_id' => $addon ] );
                }
            }

            if ( isset( $service[ 'tier_id' ] ) ) {
                OrderServiceTier::create( [ 'order_service_product_id' => $orderServiceProduct->id , 'service_tier_id' => $service[ 'tier_id' ] ] );
            }

            // Handle stock decrement for products consumed by the service
            $serviceModel = Service::with( 'serviceProducts.product' )->find( $service[ 'item_id' ] );
            if ( $serviceModel && $serviceModel->serviceProducts->isNotEmpty() ) {
                foreach ( $serviceModel->serviceProducts as $consumedProduct ) {
                    $item = $consumedProduct->product;
                    if ( ! $item ) {
                        continue;
                    }
                    $quantity = $consumedProduct->quantity * $service[ 'quantity' ];

                    if ( ! $is_preorder && $item->stock < $quantity ) {
                        $name = $item instanceof ProductVariation
                            ? $item->product->name . ' (' . $item->productAttributeOption?->name . ')'
                            : $item->name;
                        throw new Exception( "{$name} stock not enough" );
                    }

                    $stock = Stock::where( [
                        'item_id'      => $item->id ,
                        'item_type'    => get_class( $item ) ,
                        'status'       => StockStatus::RECEIVED ,
                        'warehouse_id' => $warehouse_id
                    ] )->first();

                    if ( $stock ) {
                        if ( ! $is_preorder ) {
                            $stock->decrement( 'quantity' , $quantity );
                        }
                        if ( $status == SaleOrderType::DEPOSIT->value ) {
                            $stock->increment( 'quantity_ordered' , $quantity );
                        }
                    }
                }
            }
        }

        private function handleProductQuotationItem(Order $order , array $product) : void
        {
            $is_variation = isset( $product[ 'variation_id' ] );
            $variation    = NULL;
            $targetClass  = Product::class;
            $itemId       = $product[ 'item_id' ];

            if ( $is_variation ) {
                $variation_id = $product[ 'variation_id' ];
                $variation    = ProductVariation::find( $variation_id );
                if ( $variation ) {
                    $targetClass = ProductVariation::class;
                    $itemId      = $variation->id;
                }
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
                'quotation_item_type'         => ItemType::PRODUCT ,
            ] );

            if ( $is_variation ) {
                $order_product->update( [ 'variation_id' => $variation_id ] );
            }
        }

        private function handleServiceQuotationItem(Order $order , array $service) : void
        {
            $orderServiceProduct = OrderServiceProduct::create( [
                'order_id'            => $order->id ,
                'service_id'          => $service[ 'item_id' ] ,
                'quantity'            => $service[ 'quantity' ] ,
                'total'               => $service[ 'quantity' ] * $service[ 'unitPrice' ] ,
                'unit_price'          => $service[ 'unitPrice' ] ,
                'quotation_item_type' => ItemType::SERVICE ,
            ] );

            if ( isset( $service[ 'addons' ] ) ) {
                foreach ( $service[ 'addons' ] as $addon ) {
                    OrderServiceAdon::create( [ 'order_service_product_id' => $orderServiceProduct->id , 'addon_id' => $addon ] );
                }
            }

            if ( isset( $service[ 'tier_id' ] ) ) {
                OrderServiceTier::create( [ 'order_service_product_id' => $orderServiceProduct->id , 'service_tier_id' => $service[ 'tier_id' ] ] );
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
                    $warehouse_id     = $request->warehouse_id;

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
                            'warehouse_id'    => $warehouse_id ,
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

                    // Revert stock for old products
                    foreach ( $order->orderProducts as $oldProduct ) {
                        $stock = Stock::where( [
                            'item_id'      => $oldProduct->item_id ,
                            'item_type'    => $oldProduct->item_type ,
                            'status'       => StockStatus::RECEIVED ,
                            'warehouse_id' => $order->warehouse_id
                        ] )->first();

                        if ( ! $order->pre_order_status ) {
                            $stock?->increment( 'quantity' , $oldProduct->quantity );
                        }
                    }

                    // Revert stock for old service products if they consumed stock
                    foreach ( $order->orderServiceProducts as $oldServiceProduct ) {
                        $serviceModel = Service::with( 'serviceProducts.product' )->find( $oldServiceProduct->service_id );
                        if ( $serviceModel && $serviceModel->serviceProducts->isNotEmpty() ) {
                            foreach ( $serviceModel->serviceProducts as $consumedProduct ) {
                                $stock = Stock::where( [
                                    'item_id'      => $consumedProduct->product->id ,
                                    'item_type'    => get_class( $consumedProduct->product ) ,
                                    'status'       => StockStatus::RECEIVED ,
                                    'warehouse_id' => $order->warehouse_id
                                ] )->first();
                                if ( ! $order->pre_order_status ) {
                                    $stock?->increment( 'quantity' , $consumedProduct->quantity * $oldServiceProduct->quantity );
                                }
                            }
                        }
                    }

                    $order->orderProducts()->delete();
                    $order->orderServiceProducts()->delete(); // Delete old service products
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

                    $items = json_decode( $request->items , TRUE );
                    if ( ! blank( $items ) ) {
                        foreach ( $items as $item ) {
                            if ( $item[ 'itemType' ] === ItemType::PRODUCT->value ) {
                                $this->handlePosProductItem( $order , $item , $is_preorder , $warehouse_id , $status );
                            }
                            elseif ( $item[ 'itemType' ] === ItemType::SERVICE->value ) {
                                $this->handlePosServiceItem( $order , $item , $is_preorder , $warehouse_id , $status );
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

        public function makeQuotationSale(Order $order , Request $request)
        {
            try {
                return DB::transaction( function () use ($request , $order) {
                    $order->load( 'orderProducts.item' , 'orderServiceProducts.service.items.item' );
                    $status      = $request->integer( 'status' );
                    $paymentType = $request->integer( 'paymentType' );

                    if ( $order->quotation_type === QuotationType::PRODUCT || $order->quotation_type === QuotationType::COMBINED ) {
                        foreach ( $order->orderProducts as $orderProduct ) {
                            $this->checkAndDecrementStock( $orderProduct->item , $orderProduct->quantity , $order->warehouse_id );
                        }
                    }

                    if ( $order->quotation_type === QuotationType::SERVICE || $order->quotation_type === QuotationType::COMBINED ) {
                        foreach ( $order->orderServiceProducts as $orderServiceProduct ) {
                            foreach ( $orderServiceProduct->service->items as $item ) {
                                $this->checkAndDecrementStock( $item->item , $item->quantity , $order->warehouse_id );
                            }
                        }
                    }

                    activityLog( 'Converted Quotation to sale :' . $order->order_serial_no );
                    $user   = $order->user;
                    $ledger = NULL;
                    if ( $paymentType == PaymentType::CREDIT->value || $paymentType == PaymentType::DEPOSIT->value ) {
                        $ledger = addToLedger( user: $user , reference: 'Quotation converted' , bill_amount: $order->total , paid: 0 );
                    }
                    $payments = json_decode( $request->payments , TRUE );

                    $paymentStatus = match ( $status ) {
                        SaleOrderType::COMPLETED->value => PaymentStatus::PAID ,
                        default                         => PaymentStatus::UNPAID
                    };

                    if ( $status == SaleOrderType::DEPOSIT->value ) {
                        $paymentStatus = PaymentStatus::PARTIALLY_PAID;
                    }

                    $order->update( [
                        'quotation_status' => QuotationStatus::CONVERTED ,
                        'status'           => $status == SaleOrderType::COMPLETED->value ? OrderStatus::COMPLETED->value : OrderStatus::ACCEPT->value ,
                        'payment_status'   => $paymentStatus
                    ] );
                    if ( $order->paid >= $order->total ) $order->update( [ 'payment_status' => PaymentStatus::PAID ] );

                    foreach ( $payments as $p ) {
                        $amount     = $p[ 'amount' ];
                        $net_amount = $amount;
                        if ( $amount > 0 ) {
                            $payment = PaymentMethod::find( $p[ 'id' ] );
                            if ( $ledger ) {
                                $ledger->update( [ 'paid' => $net_amount , 'balance' => $user->credits - $net_amount ] );
                            }
                            addPayment( $order , $net_amount , $payment->id , $p[ 'reference' ] ?? time() );
                            if ( $payment->name == DefaultPaymentMethods::WALLET->value ) {
                                addToCustomerWalletTransaction(
                                    $user ,
                                    -$net_amount ,
                                    CustomerWalletTransactionType::PURCHASE ,
                                    $payment->id ,
                                    $order->order_serial_no
                                );
                            }
                        }
                    }
                    return response()->json();
                } );
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        private function checkAndDecrementStock($item , $quantity , $warehouse_id) : void
        {
            if ( $item->stock < $quantity ) {
                $name = $item instanceof ProductVariation
                    ? $item->product->name . ' (' . $item->productAttributeOption?->name . ')'
                    : $item->name;
                throw new Exception( "{$name} stock not enough" );
            }

            $stock = Stock::where( [
                'item_id'      => $item->id ,
                'item_type'    => get_class( $item ) ,
                'status'       => StockStatus::RECEIVED ,
                'warehouse_id' => $warehouse_id
            ] )->first();
            $stock?->decrement( 'quantity' , $quantity );
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

        public function show(Order $order) : OrderResource
        {
            try {
                $relations = [
                    'creditDepositPurchases.paymentMethod' ,
                    'user.addresses' ,
                    'stocks' ,
                    'user' ,
                    'creator' ,
                    'paymentMethods.paymentMethod'
                ];

                if ( $order->quotation_type === QuotationType::COMBINED ) {
                    $relations[] = 'orderServiceProducts.service';
                    $relations[] = 'orderServiceProducts.addons.addon';
                    $relations[] = 'orderServiceProducts.tier.serviceTier';
                    $relations[] = 'orderProducts.item';
                    $relations[] = 'orderProducts.product.taxes.tax';
                    $relations[] = 'orderProducts.product.unit:id,code';
                    $relations[] = 'orderProducts.product.sellingUnits:id,code';
                }
                elseif ( $order->quotation_type === QuotationType::SERVICE ) {
                    $relations[] = 'orderServiceProducts.service';
                    $relations[] = 'orderServiceProducts.addons.addon';
                    $relations[] = 'orderServiceProducts.tier.serviceTier';
                }
                else {
                    $relations[] = 'orderProducts.item';
                    $relations[] = 'orderProducts.product.taxes.tax';
                    $relations[] = 'orderProducts.product.unit:id,code';
                    $relations[] = 'orderProducts.product.sellingUnits:id,code';
                }

                return new OrderResource( $order->load( $relations ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

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

        public function destroy(Order $order) : void
        {
            try {
                DB::transaction( function () use ($order) {
                    if ( $order->posPayments ) {
                        foreach ( $order->posPayments as $posPayment ) {
                            PaymentMethodTransaction::where( 'description' , 'Order Payment #' . $order->order_serial_no )->delete();
                        }
                        $order->posPayments()->delete();
                    }

                    CreditDepositPurchase::where( 'order_id' , $order->id )->delete();

                    if ( $order->stocks ) {
                        $stockIds = $order->stocks->pluck( 'id' );
                        if ( ! blank( $stockIds ) ) {
                            StockTax::whereIn( 'stock_id' , $stockIds )->delete();
                        }
                        $order->stocks()->delete();
                    }

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
                    $order->load( 'orderProducts.item' , 'orderServiceProducts.service.items.item' );
                    $status = $request->integer( 'status' );
                    if ( $status == OrderStatus::CANCELED->value ) {
                        foreach ( $order->orderProducts as $orderProduct ) {
                            $stock = Stock::where( [
                                'item_id'      => $orderProduct->item_id ,
                                'item_type'    => $orderProduct->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id
                            ] )->first();
                            $stock?->increment( 'quantity' , $orderProduct->quantity );
                        }
                        if ( $order->orderServiceProducts->isNotEmpty() ) {
                            foreach ( $order->orderServiceProducts as $orderServiceProduct ) {
                                if ( $orderServiceProduct->service->items->isNotEmpty() ) {
                                    foreach ( $orderServiceProduct->service->items as $item ) {
                                        $stock = Stock::where( [
                                            'item_id'      => $item->item->id ,
                                            'item_type'    => get_class( $item->item ) ,
                                            'status'       => StockStatus::RECEIVED ,
                                            'warehouse_id' => $order->warehouse_id
                                        ] )->first();
                                        $stock?->increment( 'quantity' , $item->quantity );
                                    }
                                }
                            }
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

        public function sendWhatsappQuotation(Order $order) : JsonResponse
        {
            try {
                $tenant = tenant( 'id' );
                SendWhatsappQuotation::dispatch( $order , $tenant );
                return response()->json();
            } catch ( Exception $exception ) {
                info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function updateQuotationStatus(Order $order , Request $request) : object
        {
            try {
                return DB::transaction( function () use ($order , $request) {
                    $status = $request->integer( 'status' );
                    if ( $status == QuotationStatus::COUNTER_OFFER->value ) {
                        $order->update( [
                            'offer_amount'  => $request->amount ,
                            'offer_message' => $request->message ,
                        ] );
                    }
                    if ( $status == QuotationStatus::CANCELLED->value ) {
                        $order->update( [
                            'decline_message' => $request->reason ,
                        ] );
                    }

                    $order->update( [ 'quotation_status' => $status ] );

                    activityLog( "Updated Quotation Status: {$order->order_serial_no}" );

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
                        $paymentMethod = PaymentMethod::firstOrCreate( [ 'name' => $paymentMethodName ] , [ 'slug' => Str::slug( $paymentMethodName ) , 'status' => Status::ACTIVE ] );
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
                    $order->increment( 'total' , $topUpAmount );
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
                                $stock = Stock::where( [
                                    'item_id'      => $orderProduct->item_id ,
                                    'item_type'    => $orderProduct->item_type ,
                                    'status'       => StockStatus::RECEIVED ,
                                    'warehouse_id' => $order->warehouse_id
                                ] )->first();

                                $stock?->decrement( 'quantity_ordered' , $diff );

                                $orderProduct->update( [ 'quantity' => $newQty , 'total' => $newQty * $orderProduct->unit_price ] );
                            }
                        }
                    }
                }

                foreach ( $order->orderProducts as $orderProduct ) {
                    $stock = Stock::where( [
                        'item_id'      => $orderProduct->item_id ,
                        'item_type'    => $orderProduct->item_type ,
                        'status'       => StockStatus::RECEIVED ,
                        'warehouse_id' => $order->warehouse_id
                    ] )->first();

                    if ( $stock ) {
                        $stock->decrement( 'quantity' , $orderProduct->quantity );
                        $stock->decrement( 'quantity_ordered' , $orderProduct->quantity );
                    }
                }

                $order->update( [
                    'pre_order_status' => PreOrderStatus::FULFILLED ,
                    'status'           => OrderStatus::COMPLETED ,
                    'payment_status'   => PaymentStatus::PAID
                ] );
            } );
        }
    }
