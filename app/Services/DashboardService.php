<?php

    namespace App\Services;

    use App\Enums\PaymentStatus;
    use App\Enums\Role as EnumRole;
    use App\Http\Resources\PaymentMethodResource;
    use App\Libraries\AppLibrary;
    use App\Models\Expense;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\Product;
    use App\Models\User;
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Number;

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

                $totalUnpaidInvoices = $unpaidInvoicesQuery->sum( 'total' ) - $unpaidInvoicesQuery->sum( 'paid' );

                // Overdue Invoices (Due date < Today)
                $overdueInvoices = Order::whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                        ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                        ->where( 'due_date' , '<' , Carbon::now() )
                                        ->get();
                $overdueAmount   = 0;
                foreach ( $overdueInvoices as $invoice ) {
                    $overdueAmount += ( $invoice->total - $invoice->paid );
                }

                // Not Due Yet Invoices (Due date >= Today)
                $notDueInvoices = Order::whereIn( 'payment_status' , [ PaymentStatus::UNPAID , PaymentStatus::PARTIALLY_PAID ] )
                                       ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] )
                                       ->where( 'due_date' , '>=' , Carbon::now() )
                                       ->get();
                $notDueAmount   = 0;
                foreach ( $notDueInvoices as $invoice ) {
                    $notDueAmount += ( $invoice->total - $invoice->paid );
                }

                // Deposit Orders (Assuming specific logic for deposits, e.g., order_type or just partial payments)
                // For this example, let's assume 'PARTIALLY_PAID' orders are deposit orders or there's a specific flag.
                // If there isn't a specific 'DEPOSIT' type in OrderType enum provided in context, we might need to infer.
                // However, the user prompt mentions "Deposit Orders". Let's assume orders with partial payments.
                $depositOrdersQuery = Order::where( 'payment_status' , PaymentStatus::PARTIALLY_PAID )
                                           ->whereBetween( 'order_datetime' , [ $startDate , $endDate ] );

                $totalDepositOrdersValue = $depositOrdersQuery->sum( 'total' );
                $paidDepositAmount       = $depositOrdersQuery->sum( 'paid' );
                $unpaidDepositBalance    = $totalDepositOrdersValue - $paidDepositAmount;


                return [
                    'unpaid_invoices' => [
                        'total'       => Number::forHumans( $totalUnpaidInvoices ) ,
                        'overdue'     => AppLibrary::currencyAmountFormat( $overdueAmount ) ,
                        'not_due_yet' => AppLibrary::currencyAmountFormat( $notDueAmount ) ,
                        'percentages' => [
                            'overdue'     => $totalUnpaidInvoices > 0 ? round( ( $overdueAmount / $totalUnpaidInvoices ) * 100 , 1 ) : 0 ,
                            'not_due_yet' => $totalUnpaidInvoices > 0 ? round( ( $notDueAmount / $totalUnpaidInvoices ) * 100 , 1 ) : 0 ,
                        ]
                    ] ,
                    'deposit_orders'  => [
                        'total'          => AppLibrary::currencyAmountFormat( $totalDepositOrdersValue ) ,
                        'paid_deposit'   => AppLibrary::currencyAmountFormat( $paidDepositAmount ) ,
                        'unpaid_balance' => AppLibrary::currencyAmountFormat( $unpaidDepositBalance ) ,
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

                $salesChart = [];
                $period     = CarbonPeriod::create( $startDate , $endDate );
                foreach ( $period as $date ) {
                    $salesChart[] = (float) Order::where( 'payment_status' , PaymentStatus::PAID )
                                                 ->whereDate( 'order_datetime' , $date )
                                                 ->sum( 'total' );
                }

                // Customers (New)
                $currentCustomers = User::role( EnumRole::CUSTOMER )
                                        ->whereBetween( 'created_at' , [ $startDate , $endDate ] )
                                        ->count();

                $prevCustomers = User::role( EnumRole::CUSTOMER )
                                     ->whereBetween( 'created_at' , [ $prevStartDate , $prevEndDate ] )
                                     ->count();

                $customersChange = $prevCustomers > 0 ? ( ( $currentCustomers - $prevCustomers ) / $prevCustomers ) * 100 : ( $currentCustomers > 0 ? 100 : 0 );

                $customersChart = [];
                foreach ( $period as $date ) {
                    $customersChart[] = User::role( EnumRole::CUSTOMER )
                                            ->whereDate( 'created_at' , $date )
                                            ->count();
                }

                // Products (New)
                $currentProducts = Product::whereBetween( 'created_at' , [ $startDate , $endDate ] )->count();
                $prevProducts    = Product::whereBetween( 'created_at' , [ $prevStartDate , $prevEndDate ] )->count();
                $productsChange  = $prevProducts > 0 ? ( ( $currentProducts - $prevProducts ) / $prevProducts ) * 100 : ( $currentProducts > 0 ? 100 : 0 );

                $productsChart = [];
                foreach ( $period as $date ) {
                    $productsChart[] = Product::whereDate( 'created_at' , $date )->count();
                }

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
    }
