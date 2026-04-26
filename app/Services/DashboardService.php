<?php

    namespace App\Services;

    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\Role as EnumRole;
    use App\Http\Resources\PaymentMethodResource;
    use App\Libraries\AppLibrary;
    use App\Models\Damage;
    use App\Models\Expense;
    use App\Models\Order;
    use App\Models\OrderProduct;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\Stock;
    use App\Models\User;
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class DashboardService
    {
        public function paymentMethods(Request $request) : AnonymousResourceCollection
        {
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function invoiceDeposit(Request $request)
        {
            try {
                $start = $request->date( 'start' );
                $end   = $request->date( 'end' );
                if ( $start && $end ) {
                    $startDate = $start->copy()->startOfDay();
                    $endDate   = $end->copy()->endOfDay();
                }
                else {
                    $startDate = Carbon::now()->copy()->startOfMonth();
                    $endDate   = Carbon::now()->copy()->endOfMonth();
                }

                $unpaidInvoicesQuery = Order::active()->whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                            ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );

                $totalUnpaidInvoices = $unpaidInvoicesQuery->sum( 'balance' );

                // Overdue Invoices (Due date < Today)
                $overdueInvoices = Order::active()
                                        ->whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                        ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                        ->where( 'due_date' , '<' , Carbon::now() )
                                        ->sum( 'balance' );
                $overdueAmount   = $overdueInvoices;

                // Not Due Yet Invoices (Due date >= Today)
                $notDueInvoices = Order::active()->whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                       ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                       ->where( 'due_date' , '>=' , Carbon::now() )
                                       ->sum( 'balance' );
                $notDueAmount   = $notDueInvoices;

                $depositOrdersQuery = Order::active()->where( 'payment_type' , PaymentType::DEPOSIT )
                                           ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );

                $totalDepositOrdersValue = $depositOrdersQuery->sum( 'total' );

                $paidDepositAmount = PosPayment::whereHas( 'order' , function ($query) use ($startDate , $endDate) {
                    $query->where( 'payment_type' , PaymentType::DEPOSIT )
                          ->where( 'is_returned' , FALSE )
                          ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );
                } )->sum( 'amount' );

                $unpaidDepositBalance = $totalDepositOrdersValue - $paidDepositAmount;


                return [
                    'unpaid_invoices' => [
                        'total'       => format_currency_short( $totalUnpaidInvoices ) ,
                        'overdue'     => format_currency_short( $overdueAmount ) ,
                        'not_due_yet' => format_currency_short( $notDueAmount ) ,
                        'percentages' => [
                            'overdue'     => $totalUnpaidInvoices > 0 ? round( ( $overdueAmount / $totalUnpaidInvoices ) * 100 , 1 ) : 0 ,
                            'not_due_yet' => $totalUnpaidInvoices > 0 ? round( ( $notDueAmount / $totalUnpaidInvoices ) * 100 , 1 ) : 0 ,
                        ]
                    ] ,
                    'deposit_orders'  => [
                        'total'          => format_currency_short( $totalDepositOrdersValue ) ,
                        'paid_deposit'   => format_currency_short( $paidDepositAmount ) ,
                        'unpaid_balance' => format_currency_short( $unpaidDepositBalance ) ,
                        'percentages'    => [
                            'paid'   => $totalDepositOrdersValue > 0 ? round( ( $paidDepositAmount / $totalDepositOrdersValue ) * 100 , 1 ) : 0 ,
                            'unpaid' => $totalDepositOrdersValue > 0 ? round( ( $unpaidDepositBalance / $totalDepositOrdersValue ) * 100 , 1 ) : 0 ,
                        ]
                    ]
                ];

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function kpi(Request $request)
        {
            try {
                $start = $request->date( 'start' );
                $end   = $request->date( 'end' );
                if ( $start && $end ) {
                    $startDate = $start->copy()->startOfDay();
                    $endDate   = $end->copy()->endOfDay();
                }
                else {
                    $startDate = Carbon::now()->copy()->startOfMonth();
                    $endDate   = Carbon::now()->copy()->endOfMonth();
                }

                $duration      = $startDate->diffInDays( $endDate ) + 1;
                $prevStartDate = $startDate->copy()->subDays( $duration );
                $prevEndDate   = $endDate->copy()->subDays( $duration );

                $currentSales = Order::active()
                                     ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                     ->sum( 'total' );

                $prevSales = Order::active()
                                  ->whereBetween( 'order_datetime' , [ $prevStartDate , $prevEndDate ] )
                                  ->sum( 'total' );

                $salesChange = $prevSales > 0 ? ( ( $currentSales - $prevSales ) / $prevSales ) * 100 : ( $currentSales > 0 ? 100 : 0 );

                $salesChart     = [];
                $customersChart = [];
                $productsChart  = [];

                $diffInDays = $startDate->diffInDays( $endDate );

                if ( $diffInDays > 31 ) {
                    // Monthly grouping
                    $current = $startDate->copy()->startOfMonth();

                    while ( $current->lte( $endDate ) ) {
                        $monthStart = $current->copy()->startOfMonth();
                        $monthEnd   = $current->copy()->endOfMonth();

                        if ( $monthStart->lt( $startDate ) ) $monthStart = $startDate->copy();
                        if ( $monthEnd->gt( $endDate ) ) $monthEnd = $endDate->copy();

                        // Sales chart: all order totals (Cash + Credit + Deposit) — matches accrual basis
                        $salesChart[] = (float) Order::active()
                                                     ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                     ->sum( 'total' );

                        $customersChart[] = User::role( EnumRole::CUSTOMER )
                                                ->whereBetween( 'created_at' , [ $monthStart , $monthEnd ] )
                                                ->count();

                        $productsChart[] = Product::whereBetween( 'created_at' , [ $monthStart , $monthEnd ] )->count();

                        $current->addMonth();
                    }
                }
                else {
                    $period = CarbonPeriod::create( $startDate , $endDate );
                    foreach ( $period as $date ) {
                        // Sales chart: all order totals (Cash + Credit + Deposit) — matches accrual basis
                        $salesChart[] = (float) Order::active()
                                                     ->whereDate( 'order_datetime' , $date )
                                                     ->sum( 'total' );

                        $customersChart[] = User::role( EnumRole::CUSTOMER )
                                                ->whereDate( 'created_at' , $date )
                                                ->count();

                        $productsChart[] = Product::whereDate( 'created_at' , $date )->count();
                    }
                }

                // Customers (New)
                $currentCustomers = User::role( EnumRole::CUSTOMER )
                                        ->whereBetween( 'created_at' , [ $startDate , $endDate ] )
                                        ->count();

                $prevCustomers = User::role( EnumRole::CUSTOMER )
                                     ->whereBetween( 'created_at' , [ $prevStartDate , $prevEndDate ] )
                                     ->count();

                $customersChange = $prevCustomers > 0 ? ( ( $currentCustomers - $prevCustomers ) / $prevCustomers ) * 100 : ( $currentCustomers > 0 ? 100 : 0 );

                // Products (New)
                $currentProducts = Product::whereBetween( 'created_at' , [ $startDate , $endDate ] )->count();
                $prevProducts    = Product::whereBetween( 'created_at' , [ $prevStartDate , $prevEndDate ] )->count();
                $productsChange  = $prevProducts > 0 ? ( ( $currentProducts - $prevProducts ) / $prevProducts ) * 100 : ( $currentProducts > 0 ? 100 : 0 );

                // Profit & Loss — mirrors RegisterResource accrual logic:
                // $income  = total sales value (all payment types)
                // $expenses = OPERATIONAL expenses only, by expense date (not created_at)
                // $profit  = $income - cost_of_goods  (cost not available at dashboard level, so gross = income here)
                // $net_profit = $profit - $expenses
                $income   = $currentSales;
                $expenses = Expense::whereBetween( 'date' , [ $startDate , $endDate ] )
                                   ->where( 'expense_nature' , \App\Enums\ExpenseNature::OPERATIONAL )
                                   ->sum( 'amount' );
                $netProfit = $income - $expenses;
                $margin    = $income > 0 ? ( $netProfit / $income ) * 100 : 0;

                return [
                    'sales'       => [
                        'value'  => currency( $currentSales ) ,
                        'change' => round( $salesChange , 1 ) ,
                        'chart'  => $salesChart
                    ] ,
                    'customers'   => [
                        'value'  => number_format( $currentCustomers ) ,
                        'change' => round( $customersChange , 1 ) ,
                        'chart'  => $customersChart
                    ] ,
                    'products'    => [
                        'value'  => number_format( $currentProducts ) ,
                        'change' => round( $productsChange , 1 ) ,
                        'chart'  => $productsChart
                    ] ,
                    'profit_loss' => [
                        'income'      => AppLibrary::currencyAmountFormat( $income ) ,
                        'expenditure' => AppLibrary::currencyAmountFormat( $expenses ) ,
                        'net_profit'  => AppLibrary::currencyAmountFormat( $netProfit ) ,
                        'margin'      => round( $margin , 1 )
                    ]
                ];

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function salesOrderCharts(Request $request)
        {
            try {
                $start = $request->date( 'start' );
                $end   = $request->date( 'end' );
                if ( $start && $end ) {
                    $startDate = $start->copy()->startOfDay();
                    $endDate   = $end->copy()->endOfDay();
                }
                else {
                    $startDate = Carbon::now()->copy()->startOfMonth();
                    $endDate   = Carbon::now()->copy()->endOfMonth();
                }

                $diffInDays = $startDate->diffInDays( $endDate );
                $categories = [];

                // Sales Series
                $posSalesData     = [];
                $creditSalesData  = [];
                $depositSalesData = [];

                // Order Series
                $fullyPaidOrdersData = [];
                $creditOrdersData    = [];
                $depositOrdersData   = [];

                if ( $diffInDays > 31 ) {
                    // Monthly grouping
                    $current = $startDate->copy()->startOfMonth();

                    while ( $current->lte( $endDate ) ) {
                        $categories[] = [ 'label' => $current->format( 'M Y' ) ];

                        $monthStart = $current->copy()->startOfMonth();
                        $monthEnd   = $current->copy()->endOfMonth();

                        if ( $monthStart->lt( $startDate ) ) $monthStart = $startDate->copy();
                        if ( $monthEnd->gt( $endDate ) ) $monthEnd = $endDate->copy();

                        // Sales Queries
                        $posSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::CASH )
                                                       ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                       ->sum( 'total' );

                        $creditSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::CREDIT )
                                                          ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                          ->sum( 'total' );

                        $depositSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::DEPOSIT )
                                                           ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                           ->sum( 'total' );

                        // Order Queries
                        $fullyPaidOrdersData[] = Order::active()->where( 'payment_status' , PaymentStatus::PAID )
                                                      ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                      ->count();

                        $creditOrdersData[] = Order::active()->where( 'payment_type' , PaymentType::CREDIT )
                                                   ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                   ->where( 'is_returned' , FALSE )
                                                   ->count();

                        $depositOrdersData[] = Order::active()->where( 'payment_type' , PaymentType::DEPOSIT )
                                                    ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                    ->where( 'is_returned' , FALSE )
                                                    ->count();

                        $current->addMonth();
                    }
                }
                else {
                    // Daily grouping
                    $period = CarbonPeriod::create( $startDate , $endDate );
                    foreach ( $period as $date ) {
                        $categories[] = [ 'label' => $date->format( 'M d' ) ];

                        // Sales Queries
                        $posSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::CASH )
                                                       ->whereDate( 'order_datetime' , $date )
                                                       ->sum( 'total' );

                        $creditSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::CREDIT )
                                                          ->whereDate( 'order_datetime' , $date )
                                                          ->sum( 'total' );

                        $depositSalesData[] = (float) Order::active()->where( 'payment_type' , PaymentType::DEPOSIT )
                                                           ->whereDate( 'order_datetime' , $date )
                                                           ->sum( 'total' );

                        // Order Queries
                        $fullyPaidOrdersData[] = Order::active()->where( 'payment_status' , PaymentStatus::PAID )
                                                      ->whereDate( 'order_datetime' , $date )
                                                      ->count();

                        $creditOrdersData[] = Order::active()->where( 'payment_type' , PaymentType::CREDIT )
                                                   ->whereDate( 'order_datetime' , $date )
                                                   ->count();

                        $depositOrdersData[] = Order::active()->where( 'payment_type' , PaymentType::DEPOSIT )
                                                    ->whereDate( 'order_datetime' , $date )
                                                    ->count();
                    }
                }

                $salesSeries = [
                    [ 'name' => 'POS Sales' , 'data' => $posSalesData ] ,
                    [ 'name' => 'Credit Sales' , 'data' => $creditSalesData ] ,
                    [ 'name' => 'Deposit Sales' , 'data' => $depositSalesData ]
                ];

                $orderSeries = [
                    [ 'name' => 'Fully Paid' , 'data' => $fullyPaidOrdersData ] ,
                    [ 'name' => 'Credit' , 'data' => $creditOrdersData ] ,
                    [ 'name' => 'Deposit' , 'data' => $depositOrdersData ]
                ];

                return [
                    'categories'  => $categories ,
                    'salesSeries' => $salesSeries ,
                    'orderSeries' => $orderSeries ,
                    'totalOrders' => number_format( Order::active()->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                                         ->count() )
                ];

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function inventoryOverview(Request $request)
        {
            try {
                $totalProducts = Product::count();
                $totalItems    = $totalProducts;

                $stocks      = Stock::with( 'product' )->get();
                $products    = Product::all();
                $stock_value = 0;
                $lowStock    = 0;
                $outOfStock  = 0;
                $inStock     = 0;

                foreach ( $products as $product ) {
                    $stock_value += $product->stock * $product->buying_price;
                    if ( $product->stock < $product->low_stock_quantity_warning ) {
                        $lowStock += 1;
                    }
                    if ( $product->stock > $product->low_stock_quantity_warning && $product->stock > 0 ) {
                        $inStock += 1;
                    }
                    if ( $product->stock == 0 ) {
                        $outOfStock += 1;
                    }
                }
                $damages = Damage::whereHas( 'stocks' , function ($q) {
                    $q->whereHas( 'products' );
                } )->count();

                $uniqueExpiredProducts = [];

                foreach ( $stocks as $stock ) {
                    if ( $stock->expiry_date && $stock->expiry_date->isPast() ) {
                        $uniqueExpiredProducts[ $stock->product_id ] = TRUE;
                    }
                }
                $expired = count( $uniqueExpiredProducts );

                $totalItems = $totalItems > 0 ? $totalItems : 1;

                return [
                    'stock_value' => AppLibrary::currencyAmountFormat( $stock_value ) ,
                    'items'       => [
                        [
                            'label' => 'In Stock' ,
                            'val'   => number_format( $inStock ) . ' items (' . round( ( $inStock / $totalItems ) * 100 , 1 ) . '%)' ,
                            'pct'   => round( ( $inStock / $totalItems ) * 100 , 1 ) . '%' ,
                            'color' => 'bg-green-500'
                        ] ,
                        [
                            'label' => 'Low Stock' ,
                            'val'   => number_format( $lowStock ) . ' items (' . round( ( $lowStock / $totalItems ) * 100 , 1 ) . '%)' ,
                            'pct'   => round( ( $lowStock / $totalItems ) * 100 , 1 ) . '%' ,
                            'color' => 'bg-yellow-500'
                        ] ,
                        [
                            'label' => 'Out of Stock' ,
                            'val'   => number_format( $outOfStock ) . ' items (' . round( ( $outOfStock / $totalItems ) * 100 , 1 ) . '%)' ,
                            'pct'   => round( ( $outOfStock / $totalItems ) * 100 , 1 ) . '%' ,
                            'color' => 'bg-red-500'
                        ] ,
                        [
                            'label' => 'Expired/Damaged' ,
                            'val'   => number_format( $expired + $damages ) . ' items (' . round( ( ( $expired + $damages ) / $totalItems ) * 100 , 1 ) . '%)' ,
                            'pct'   => round( ( ( $expired + $damages ) / $totalItems ) * 100 , 1 ) . '%' ,
                            'color' => 'bg-gray-400'
                        ]
                    ]
                ];

            } catch ( Exception $exception ) {
                Log::error( 'Inventory Overview Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function expenses(Request $request)
        {
            try {
                $start = $request->date( 'start' );
                $end   = $request->date( 'end' );
                if ( $start && $end ) {
                    $startDate = $start->copy()->startOfDay();
                    $endDate   = $end->copy()->endOfDay();
                }
                else {
                    $startDate = Carbon::now()->copy()->startOfMonth();
                    $endDate   = Carbon::now()->copy()->endOfMonth();
                }

                $diffInDays = $startDate->diffInDays( $endDate );
                $categories = [];

                $totalExpensesData  = [];
                $paidExpensesData   = [];
                $unpaidExpensesData = [];

                if ( $diffInDays > 31 ) {
                    // Monthly grouping
                    $current = $startDate->copy()->startOfMonth();
                    while ( $current->lte( $endDate ) ) {
                        $categories[] = [ 'label' => $current->format( 'M Y' ) ];

                        $monthStart = $current->copy()->startOfMonth();
                        $monthEnd   = $current->copy()->endOfMonth();

                        if ( $monthStart->lt( $startDate ) ) $monthStart = $startDate->copy();
                        if ( $monthEnd->gt( $endDate ) ) $monthEnd = $endDate->copy();

                        $stats = Expense::whereBetween( 'date' , [ $monthStart , $monthEnd ] )
                                        ->selectRaw( 'sum(amount) as total, sum(paid) as paid_amount' )
                                        ->first();

                        $total  = (float) ( $stats->total ?? 0 );
                        $paid   = (float) ( $stats->paid_amount ?? 0 );
                        $unpaid = $total - $paid;

                        $totalExpensesData[]  = $total;
                        $paidExpensesData[]   = $paid;
                        $unpaidExpensesData[] = $unpaid;

                        $current->addMonth();
                    }
                }
                else {
                    // Daily grouping
                    $period = CarbonPeriod::create( $startDate , $endDate );
                    foreach ( $period as $date ) {
                        $categories[] = [ 'label' => $date->format( 'M d' ) ];

                        $stats = Expense::whereDate( 'date' , $date )
                                        ->selectRaw( 'sum(amount) as total, sum(paid) as paid_amount' )
                                        ->first();

                        $total  = (float) ( $stats->total ?? 0 );
                        $paid   = (float) ( $stats->paid_amount ?? 0 );
                        $unpaid = $total - $paid;

                        $totalExpensesData[]  = $total;
                        $paidExpensesData[]   = $paid;
                        $unpaidExpensesData[] = $unpaid;
                    }
                }

                $distributionData = Expense::whereBetween( 'date' , [ $startDate , $endDate ] )
                                           ->select( 'expense_category_id' , DB::raw( 'sum(amount) as total' ) )
                                           ->groupBy( 'expense_category_id' )
                                           ->with( 'expenseCategory' )
                                           ->get()
                                           ->map( function ($row) {
                                               return [
                                                   'name'  => $row->expenseCategory?->name ?? 'Uncategorized' ,
                                                   'value' => (float) $row->total
                                               ];
                                           } )
                                           ->sortByDesc( 'value' )
                                           ->values();

                $totalPeriodExpenses = Expense::whereBetween( 'date' , [ $startDate , $endDate ] )->sum( 'amount' );

                return [
                    'overview'     => [
                        'categories' => $categories ,
                        'series'     => [
                            [ 'name' => 'Total Expenses' , 'type' => 'column' , 'data' => $totalExpensesData ] ,
                            [ 'name' => 'Paid' , 'type' => 'area' , 'data' => $paidExpensesData ] ,
                            [ 'name' => 'Unpaid' , 'type' => 'line' , 'data' => $unpaidExpensesData ] ,
                        ]
                    ] ,
                    'distribution' => [
                        'labels' => $distributionData->pluck( 'name' ) ,
                        'series' => $distributionData->pluck( 'value' )
                    ] ,
                    'total'        => currency( $totalPeriodExpenses )
                ];

            } catch ( Exception $exception ) {
                Log::error( 'Expenses Chart Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function topSellingProducts(Request $request)
        {
            try {
                $start = $request->date( 'start' ) ?? now()->startOfMonth();
                $end   = $request->date( 'end' ) ?? now()->endOfMonth();

                return OrderProduct::query()
                                   ->select( 'item_id' , 'item_type' )
                                   ->selectRaw( 'SUM(quantity) as total_sold' )
                                   ->selectRaw( 'SUM(total) as total_revenue' )
                    // Only count products from completed/paid orders
                                   ->whereHas( 'order' , function ($query) use ($start , $end) {
                        $query->whereIn( 'payment_status' , [ PaymentStatus::PAID , PaymentStatus::PARTIALLY_PAID ] )
                              ->whereBetween( 'order_datetime' , [ $start , $end ] );
                    } )
                                   ->groupBy( 'item_id' , 'item_type' )
                                   ->orderByDesc( 'total_sold' )
                                   ->limit( 5 )
                                   ->get()
                                   ->map( function ($row) {
                                       // Load the product or variation
                                       $item = $row->item;

                                       return [
                                           'name'     => $item->name ?? 'Unknown Product' ,
                                           'image'    => $item->image ,
                                           'category' => $item->category?->name ?? 'General' ,
                                           'sold'     => (int) $row->total_sold ,
                                           // Using your format_currency_short function for values like "UGX 264M"
                                           'revenue'  => format_currency_short( $row->total_revenue ) ,
                                           'trend'    => '0%' , // Trend requires comparison with previous period logic
                                       ];
                                   } );
            } catch ( \Exception $e ) {
                Log::error( 'Top Selling Products Error: ' . $e->getMessage() );
                return [];
            }
        }

        public function purchases(Request $request)
        {
            try {
                $start = $request->date( 'start' );
                $end   = $request->date( 'end' );
                if ( $start && $end ) {
                    $startDate = $start->copy()->startOfDay();
                    $endDate   = $end->copy()->endOfDay();
                }
                else {
                    $startDate = Carbon::now()->copy()->startOfMonth();
                    $endDate   = Carbon::now()->copy()->endOfMonth();
                }

                $purchasesQuery = Purchase::whereBetween( 'date' , [ $startDate , $endDate ] );

                $totalAmount = $purchasesQuery->sum( 'total' );

                $totalPaid = $purchasesQuery->get()->sum( function (Purchase $q) {
                    return $q->purchasePayments()->sum( 'amount' );
                } );

                $balance = $totalAmount - $totalPaid;

                $totalSuppliers = $purchasesQuery->get()->unique( 'supplier_id' )->count();

                return [
                    'series'         => [ $totalPaid , $balance ] ,
                    'totalSuppliers' => $totalSuppliers ,
                    'balance'        => format_currency_short( $balance )
                ];

            } catch ( Exception $exception ) {
                Log::error( 'Purchases Chart Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }