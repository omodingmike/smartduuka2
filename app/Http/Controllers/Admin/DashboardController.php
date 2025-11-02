<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Resources\CustomerStatesResource;
    use App\Http\Resources\SalesSummaryResource;
    use App\Http\Resources\SimpleProductResource;
    use App\Libraries\AppLibrary;
    use App\Models\Expense;
    use App\Services\DashboardService;
    use App\Services\ProductService;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;

    class DashboardController extends AdminController
    {
        use ApiResponse , AuthUser;

        private DashboardService $dashboardService;
        private ProductService   $productService;

        public function __construct(DashboardService $dashboardService , ProductService $productService)
        {
            parent::__construct();
            $this->dashboardService = $dashboardService;
            $this->productService   = $productService;
        }

        public function totalSales(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'total_sales' => AppLibrary::currencyAmountFormat( ( $this->dashboardService->totalSales( $request ) ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function cards(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [
                    'total_sales'     => AppLibrary::currencyAmountFormat( ( $this->dashboardService->totalSales( $request ) ) ) ,
                    'total_customers' => ( $this->dashboardService->totalCustomers( $request ) ) ,
                    'total_orders'    => ( $this->dashboardService->totalOrders( $request ) ) ,
                    'stock_value'     => AppLibrary::currencyAmountFormat( ( $this->dashboardService->stockValue( $request ) ) ) ,
                ]
                ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function grossProfit(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'gross_profit' => AppLibrary::currencyAmountFormat( $this->dashboardService->grossProfit( $request ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function netProfit(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'net_profit' => AppLibrary::currencyAmountFormat( $this->dashboardService->netProfit( $request ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function totalOrders(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'total_orders' => $this->dashboardService->totalOrders( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function stockValue(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'stock_value' => AppLibrary::currencyAmountFormat( $this->dashboardService->stockValue( $request ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function totalCustomers(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'total_customers' => $this->dashboardService->totalCustomers( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function vendorBalance(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'vendor_balance' => AppLibrary::currencyAmountFormat( $this->dashboardService->vendorBalance( $request ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function pendingExpenses()
        {
            $total = Expense::selectRaw( 'SUM(amount - paid) as total' )
                            ->value( 'total' );
            return $this->response( TRUE , 'success' , data: [ 'pendingExpense' => number_format( $total ) ] );
        }

        public function outStock(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'out_stock' => $this->dashboardService->outStock( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function expiredStock(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'stockExpired' => number_format( $this->dashboardService->expiredStock( $request ) ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function inStock(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'in_stock' => $this->dashboardService->inStock( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function creditSales(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'credit_sales' => $this->dashboardService->creditSales( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function depositSales(Request $request
        ) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'deposit_sales' => $this->dashboardService->depositSales( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function totalProducts(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [ 'total_products' => $this->dashboardService->totalProducts( $request ) ] ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function totalExpenses(Request $request)
        {
            $total = Expense::when( $request->first_date && $request->last_date , function ($query) use ($request) {
                $start_date = Carbon::parse( $request->first_date );
                $last_date  = Carbon::parse( $request->last_date );
                $query->whereBetween( 'created_at' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ] );
            } )->sum( 'amount' );
            return $this->response( TRUE , 'success' , data: [ 'totalExpense' => number_format( $total ) ] );
        }

        public function salesSummary(
            Request $request
        ) : Response | SalesSummaryResource | Application | ResponseFactory
        {
            try {
                return new SalesSummaryResource( $this->dashboardService->salesSummary( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function customerStates(
            Request $request
        ) : Response | CustomerStatesResource | Application | ResponseFactory
        {
            try {
                return new CustomerStatesResource( $this->dashboardService->customerStates( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function topProducts() : Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return SimpleProductResource::collection( $this->productService->topProducts() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
