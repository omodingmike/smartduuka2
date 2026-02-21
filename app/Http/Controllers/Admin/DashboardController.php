<?php

    namespace App\Http\Controllers\Admin;

    use App\Services\DashboardService;
    use App\Services\ProductService;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
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

        public function index(Request $request) : Response | array | Application | ResponseFactory
        {
            try {
                return [ 'data' => [
                    'kpi'               => $this->dashboardService->kpi( $request ) ,
                    'paymentMethods'    => $this->dashboardService->paymentMethods( $request ) ,
                    'invoiceDeposit'    => $this->dashboardService->invoiceDeposit( $request ) ,
                    'salesOrderCharts'  => $this->dashboardService->salesOrderCharts( $request ) ,
                    'inventoryOverview' => $this->dashboardService->inventoryOverview( $request ) ,
                ]
                ];
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
