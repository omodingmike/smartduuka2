<?php

    namespace App\Services;

    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\Role as EnumRole;
    use App\Http\Resources\PaymentMethodResource;
    use App\Libraries\AppLibrary;
    use App\Models\Expense;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\Stock;
    use App\Models\User;
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

                // Unpaid Invoices (Assuming 'UNPAID' or 'PARTIALLY_PAID' status)
                $unpaidInvoicesQuery = Order::whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                            ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );

                $totalUnpaidInvoices = $unpaidInvoicesQuery->sum( 'balance' );

                $paidInvoiceAmount = PosPayment::whereHas( 'order' , function ($query) use ($startDate , $endDate) {
                    $query->whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                          ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );
                } )->sum( 'amount' );

                // Overdue Invoices (Due date < Today)
                $overdueInvoices = Order::whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                        ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                        ->where( 'due_date' , '<' , Carbon::now() )
                                        ->sum( 'balance' );
                $overdueAmount   = $overdueInvoices;

                // Not Due Yet Invoices (Due date >= Today)
                $notDueInvoices = Order::whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                       ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                       ->where( 'due_date' , '>=' , Carbon::now() )
                                       ->sum( 'balance' );
                $notDueAmount   = $notDueInvoices;

                // Deposit Orders (Assuming specific logic for deposits, e.g., order_type or just partial payments)
                // For this example, let's assume 'PARTIALLY_PAID' orders are deposit orders or there's a specific flag.
                // If there isn't a specific 'DEPOSIT' type in OrderType enum provided in context, we might need to infer.
                // However, the user prompt mentions "Deposit Orders". Let's assume orders with partial payments.
                $depositOrdersQuery = Order::where( 'payment_type' , PaymentType::DEPOSIT )
                                           ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );

                $totalDepositOrdersValue = $depositOrdersQuery->sum( 'total' );

                // Calculate paid amount for deposit orders using PosPayment model
                // We need to join with orders table to filter by date and payment type
                $paidDepositAmount = PosPayment::whereHas( 'order' , function ($query) use ($startDate , $endDate) {
                    $query->where( 'payment_type' , PaymentType::DEPOSIT )
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

                // Sales
                $currentSales = Order::where( 'payment_status' , PaymentStatus::PAID )
                                     ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                     ->sum( 'total' );

                $prevSales = Order::where( 'payment_status' , PaymentStatus::PAID )
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

                        $salesChart[] = (float) Order::where( 'payment_status' , PaymentStatus::PAID )
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
                        $salesChart[] = (float) Order::where( 'payment_status' , PaymentStatus::PAID )
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

                // Profit & Loss
                $income    = $currentSales;
                $expenses  = Expense::whereBetween( 'created_at' , [ $startDate , $endDate ] )->sum( 'amount' );
                $netProfit = $income - $expenses;
                $margin    = $income > 0 ? ( $netProfit / $income ) * 100 : 0;

                return [
                    'sales'       => [
                        'value'  => AppLibrary::currencyAmountFormat( $currentSales ) ,
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
                        $posSalesData[] = (float) Order::where( 'payment_type' , PaymentType::CASH )
                                                       ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                       ->sum( 'total' );

                        $creditSalesData[] = (float) Order::where( 'payment_type' , PaymentType::CREDIT )
                                                          ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                          ->sum( 'total' );

                        $depositSalesData[] = (float) Order::where( 'payment_type' , PaymentType::DEPOSIT )
                                                           ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                           ->sum( 'total' );

                        // Order Queries
                        $fullyPaidOrdersData[] = Order::where( 'payment_status' , PaymentStatus::PAID )
                                                      ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                      ->count();

                        $creditOrdersData[] = Order::where( 'payment_type' , PaymentType::CREDIT )
                                                   ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
                                                   ->count();

                        $depositOrdersData[] = Order::where( 'payment_type' , PaymentType::DEPOSIT )
                                                    ->whereBetween( 'order_datetime' , [ $monthStart , $monthEnd ] )
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
                        $posSalesData[] = (float) Order::where( 'payment_type' , PaymentType::CASH )
                                                       ->whereDate( 'order_datetime' , $date )
                                                       ->sum( 'total' );

                        $creditSalesData[] = (float) Order::where( 'payment_type' , PaymentType::CREDIT )
                                                          ->whereDate( 'order_datetime' , $date )
                                                          ->sum( 'total' );

                        $depositSalesData[] = (float) Order::where( 'payment_type' , PaymentType::DEPOSIT )
                                                           ->whereDate( 'order_datetime' , $date )
                                                           ->sum( 'total' );

                        // Order Queries
                        $fullyPaidOrdersData[] = Order::where( 'payment_status' , PaymentStatus::PAID )
                                                      ->whereDate( 'order_datetime' , $date )
                                                      ->count();

                        $creditOrdersData[] = Order::where( 'payment_type' , PaymentType::CREDIT )
                                                   ->whereDate( 'order_datetime' , $date )
                                                   ->count();

                        $depositOrdersData[] = Order::where( 'payment_type' , PaymentType::DEPOSIT )
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
                    'totalOrders' => number_format( Order::whereBetween( 'order_datetime' , [ $startDate , $endDate ] )->count() )
                ];

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function inventoryOverview(Request $request)
        {
            try {
                $totalStockValue = 0;
                $totalItems      = 0;
                $inStock         = 0;
                $lowStock        = 0;
                $outOfStock      = 0;
                $expired         = 0;

                // Assuming you have a Stock model or similar logic to calculate stock value
                // This part needs to be adapted to your actual Stock model structure
                // Based on previous context, there is a Stock model.

                // Fetch all active stocks
                // You might want to chunk this if you have a huge number of stocks
                $stocks = Stock::with( 'product' )->get();

                foreach ( $stocks as $stock ) {
                    $quantity = $stock->quantity;
                    $price    = $stock->price; // Or cost price, depending on what "Stock Value" means to you (usually cost)

                    // If product has variations or specific pricing logic, adjust here.
                    // For now, simple quantity * price
                    $totalStockValue += ( $quantity * $price );
                    $totalItems++;

                    if ( $quantity <= 0 ) {
                        $outOfStock++;
                    }
                    elseif ( $quantity < 10 ) { // Assuming 10 is the low stock threshold, or use a setting
                        $lowStock++;
                    }
                    else {
                        $inStock++;
                    }

                    // Check for expiry if applicable
                    if ( $stock->expiry_date && Carbon::parse( $stock->expiry_date )->isPast() ) {
                        $expired++;
                    }
                }

                $totalItems = $totalItems > 0 ? $totalItems : 1;

                return [
                    'stock_value' => AppLibrary::currencyAmountFormat( $totalStockValue ) ,
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
                            'val'   => number_format( $expired ) . ' items (' . round( ( $expired / $totalItems ) * 100 , 1 ) . '%)' ,
                            'pct'   => round( ( $expired / $totalItems ) * 100 , 1 ) . '%' ,
                            'color' => 'bg-gray-400'
                        ]
                    ]
                ];

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
