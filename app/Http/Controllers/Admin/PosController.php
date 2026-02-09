<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\OrderStatus;
    use App\Enums\RegisterStatus;
    use App\Enums\StockStatus;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\PosOrderRequest;
    use App\Http\Resources\CustomerResource;
    use App\Http\Resources\OrderDetailsResource;
    use App\Http\Resources\OrderResource;
    use App\Http\Resources\RegisterResource;
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\Register;
    use App\Models\Stock;
    use App\Models\User;
    use App\Services\CommissionCalculator;
    use App\Services\CustomerService;
    use App\Services\OrderService;
    use Essa\APIToolKit\Api\ApiResponse;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;


    class PosController extends AdminController
    {
        use ApiResponse;

        private OrderService    $orderService;
        private CustomerService $customerService;

        public function __construct(OrderService $order , CustomerService $customerService)
        {
            parent::__construct();
            $this->orderService    = $order;
            $this->customerService = $customerService;
            $this->middleware( [ 'permission:pos' ] )->only( 'store' );
        }

        public function store(PosOrderRequest $request)
        {
            try {
                return new OrderResource( $this->orderService->posOrderStore( $request ) );
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

        public function openRegister(Request $request)
        {
            Register::create( [
                'opening_float' => $request->integer( 'amount' ) ,
                'status'        => RegisterStatus::OPEN ,
                'user_id'       => auth()->id()
            ] );
        }

        public function closeRegister(Request $request)
        {
            $register = auth()->user()->openRegister();

            $sales = $register->posPayments();

            $expectedFloat = ( $register->opening_float + $sales->sum( 'amount' ) );

            $difference = $request->closing_amount - $expectedFloat;

            $register->update( [
                'expected_float' => $expectedFloat ,
                'closing_float'  => $request->closing_amount ,
                'difference'     => $difference ,
                'status'         => RegisterStatus::CLOSED->value ,
                'closed_at'      => now() ,
            ] );

            if ( $request->notes ) {
                $register->update( [
                    'notes' => $request->notes
                ] );
            }

            return response()->json( [
                'message' => 'Register closed successfully' ,
                'audit'   => [
                    'expected'    => $expectedFloat ,
                    'actual'      => $request->closing_float ,
                    'discrepancy' => $difference
                ]
            ] );
        }

        public function makeSale(Request $request , CommissionCalculator $commissionCalculator)
        {
            try {
                return new OrderDetailsResource( $this->orderService->posOrderMakeSale( $request , $commissionCalculator ) );
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

        public function updateCustomer(CustomerRequest $request , User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                $customer = $this->customerService->update( $request , $customer );
                return new CustomerResource( $customer );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function index(Order $order)
        {
            return new OrderDetailsResource( $order );
        }

        public function registerDetails()
        {
            $register = auth()->user()->openRegister();
            if ( ! $register ) {
                return response()->json( [ 'message' => 'No open register found' ] , 404 );
            }
            return new RegisterResource( $register->load( [ 'user' , 'posPayments' , 'orders.orderProducts.item' , 'expenses' ] ) );
        }

        public function destroy(Request $request)
        {
            try {
                return DB::transaction( function () use ($request) {
                    $ids = $request->ids;
                    foreach ( $ids as $id ) {
                        $order = Order::find( $id );
                        $order->posPayments()->delete();
                        $order->paymentMethodTransactions()->delete();
                        $order->orderProducts()->delete();
                        foreach ( $order->orderProducts as $order_product ) {
                            $stock = Stock::where( [ 'item_type' => Product::class , 'item_id' => $order_product->item_id ] )->first();
                            $stock->increment( 'quantity' , $order_product->quantity );
                        }
                        activity()->on( auth()->user() )->log( "Deleted Order: " . $order->order_serial_no );
                        $order->delete();
                    }
                    return response()->json( [ 'status' => TRUE , 'message' => 'Orders deleted successfully' ] );
                } );
            } catch ( Exception $e ) {
                return $this->APIError( 422 , 'Error' , $e->getMessage() );
            }
        }
    }
