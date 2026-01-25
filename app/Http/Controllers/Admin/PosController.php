<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\OrderStatus;
    use App\Enums\StockStatus;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\PosOrderRequest;
    use App\Http\Resources\CustomerResource;
    use App\Http\Resources\OrderDetailsResource;
    use App\Http\Resources\OrderResource;
    use App\Models\Order;
    use App\Services\CommissionCalculator;
    use App\Services\CustomerService;
    use App\Services\OrderService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;


    class PosController extends AdminController
    {
        private OrderService    $orderService;
        private CustomerService $customerService;

        public function __construct(OrderService $order , CustomerService $customerService)
        {
            parent::__construct();
            $this->orderService    = $order;
            $this->customerService = $customerService;
            $this->middleware( [ 'permission:pos' ] )->only( 'store' );
        }

        public function store(PosOrderRequest $request , CommissionCalculator $commissionCalculator)
        {
            try {
                return new OrderDetailsResource( $this->orderService->posOrderStore( $request , $commissionCalculator ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(PosOrderRequest $request) : Response | OrderDetailsResource | Application | ResponseFactory
        {
            try {
                return new OrderDetailsResource( $this->orderService->posOrderUpdate( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function makeSale(Request $request,CommissionCalculator $commissionCalculator)
        {
            try {
                return new OrderDetailsResource( $this->orderService->posOrderMakeSale( $request,$commissionCalculator ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function cancel(Request $request)
        {
            try {
                $order = Order::find( $request->order_id );
                $order->update( [ 'status' => OrderStatus::CANCELED ] );
                $order->stocks()->update( [ 'status' => StockStatus::CANCELED ] );
                return new OrderResource( $order );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function storeCustomer(CustomerRequest $request
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                $customer = $this->customerService->store( $request );
                return new CustomerResource( $customer );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function index(Order $order) {
            return new OrderDetailsResource($order);
        }
    }
