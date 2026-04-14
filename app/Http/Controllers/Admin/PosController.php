<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\OrderStatus;
    use App\Enums\RefundStatus;
    use App\Enums\RegisterStatus;
    use App\Enums\ReturnStatus;
    use App\Enums\ReturnType;
    use App\Enums\StockStatus;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\OrderReturnRequest;
    use App\Http\Requests\PosOrderRequest;
    use App\Http\Resources\CustomerResource;
    use App\Http\Resources\OrderDetailsResource;
    use App\Http\Resources\OrderResource;
    use App\Http\Resources\RegisterResource;
    use App\Models\Damage;
    use App\Models\Order;
    use App\Models\PaymentMethodTransaction;
    use App\Models\PosPayment;
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

        public function returnOrderStore(OrderReturnRequest $request)
        {
            try {
                return new OrderResource( $this->orderService->returnOrderStore( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function returnOrderStatus(Request $request , Order $order)
        {
            try {
                $status = $request->integer( 'status' );

                if ( $status == ReturnStatus::APPROVED->value && $order->return_status !== ReturnStatus::APPROVED->value ) {
                    foreach ( $order->orderProducts as $order_product ) {
                        $stock = Stock::where( [
                            'item_id'      => $order_product->item_id ,
                            'item_type'    => $order_product->item_type ,
                            'warehouse_id' => $order->warehouse_id ,
                            'status'       => StockStatus::RECEIVED
                        ] )->first();

                        if ( $order_product->is_return && $stock && $order_product->return_type->value == ReturnType::RESELLABLE->value ) {
                            info( 'increment stock.' );
                            $stock->increment( 'quantity' , $order_product->return_quantity );
                        }

                        if ( $order_product->is_return && $order_product->return_type->value == ReturnType::DAMAGED->value ) {
                            $damage = Damage::create( [
                                'date'         => now() ,
                                'reference_no' => 'D-' . time() ,
                                'subtotal'     => 0 ,
                                'creator_id'   => auth()->id() ,
                                'tax'          => 0 ,
                                'discount'     => 0 ,
                                'total'        => 0 ,
                                'note'         => '' ,
                                'reason'       => $order->reason
                            ] );

                            Stock::create( [
                                'model_type'      => Damage::class ,
                                'model_id'        => $damage->id ,
                                'warehouse_id'    => $order->warehouse_id ,
                                'item_type'       => $order_product->item_type ,
                                'product_id'      => $order_product->id ,
                                'variation_names' => 'variation_names' ,
                                'item_id'         => $order_product->id ,
                                'price'           => 0 ,
                                'quantity'        => -$order_product->return_quantity ,
                                'discount'        => 0 ,
                                'tax'             => 0 ,
                                'subtotal'        => 0 ,
                                'total'           => 0 ,
                                'sku'             => 'sku' ,
                                'status'          => StockStatus::RECEIVED
                            ] );
                        }
                    }

//                    $total_returns  = $order->orderProducts()->where( 'is_return' , TRUE )->sum( 'total' );
//                    $total_exchange = $order->orderProducts()->where( 'is_exchange' , TRUE )->sum( 'total' );
//                    $balance        = $total_returns - $total_exchange;

//                    if ( $balance > 0 ) {
//                        PosPayment::create( [
//                            'order_id'          => $order->id ,
//                            'date'              => now() ,
//                            'reference_no'      => time() ,
//                            'amount'            => -$balance ,
//                            'payment_method_id' => $order->payment_method ,
//                            'register_id'       => register()->id
//                        ] );
//
//                        PaymentMethodTransaction::create( [
//                            'amount'            => $balance ,
//                            'item_type'         => Order::class ,
//                            'item_id'           => $order->id ,
//                            'charge'            => 0 ,
//                            'description'       => 'Order Return/Exchange #' . $order->order_serial_no ,
//                            'payment_method_id' => $order->payment_method ,
//                        ] );
//                    }
                }

                if ( $status == ReturnStatus::REJECTED->value || $status == ReturnStatus::CANCELED->value ) {
                    $order->originalOrder()->update( [ 'is_returned' => FALSE ] );
                }
                $order->update( [ 'return_status' => $status ] );

            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function returnOrderRefundPaymentStatus(Order $order , Request $request)
        {
            try {
                DB::transaction( function () use ($order , $request) {
                    $payment_method = $request->integer( 'payment_method' );
                    PosPayment::create( [
                        'order_id'          => $order->id ,
                        'date'              => now() ,
                        'reference_no'      => time() ,
                        'amount'            => $order->total ,
                        'payment_method_id' => $payment_method ,
                        'register_id'       => register()->id
                    ] );

                    PaymentMethodTransaction::create( [
                        'amount'            => $order->total ,
                        'item_type'         => Order::class ,
                        'item_id'           => $order->id ,
                        'charge'            => 0 ,
                        'description'       => 'Order Return/Exchange #' . $order->order_serial_no ,
                        'payment_method_id' => $payment_method ,
                    ] );
                    $order->update( [ 'refund_status' => RefundStatus::REFUNDED ] );
                } );

            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(Order $order , PosOrderRequest $request)
        {
            try {
                return new OrderResource( $this->orderService->posOrderUpdate( $order , $request ) );
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
            $register       = register();
            $closing_amount = $request->integer( 'closing_amount' );

            // 1. Total money that came INTO the drawer (Sales + Debt Recoveries)
            $money_in = $register->posPayments()->sum( 'amount' );

            // 2. Total money that left the drawer (Cash Expenses/Payouts)
            // We sum all expense payments tied to this register
            $money_out = $register->expensesPayments()->sum( 'amount' );

            // 3. True Expected Drawer Cash
            $expectedFloat = $register->opening_float + $money_in - $money_out;

            $difference = $closing_amount - $expectedFloat;

            $register->update( [
                'expected_float' => $expectedFloat ,
                'closing_float'  => $closing_amount ,
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
                    'actual'      => $closing_amount ,
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
                        activity()->on( auth()->user() )->log( 'Deleted Order: ' . $order->order_serial_no );
                        $order->delete();
                    }
                    return response()->json( [ 'status' => TRUE , 'message' => 'Orders deleted successfully' ] );
                } );
            } catch ( Exception $e ) {
                return $this->APIError( 422 , 'Error' , $e->getMessage() );
            }
        }

        public function deleteRefundOrder(Request $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $ids = $request->array( 'ids' );
                    foreach ( $ids as $id ) {
                        $order = Order::find( $id );
                        $order->originalOrder()->update( [ 'is_returned' => FALSE ] );
                        $order->delete();
                    }
                } );
            } catch ( Exception $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }
    }
