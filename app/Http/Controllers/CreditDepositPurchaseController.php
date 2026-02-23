<?php

    namespace App\Http\Controllers;

    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Http\Resources\OrderResource;
    use App\Libraries\AppLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PaymentMethodTransaction;
    use App\Models\PosPayment;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class CreditDepositPurchaseController extends Controller
    {
        public function index($orderId)
        {
            // Fetch the CreditDepositPurchase based on the provided order ID
            $creditDepositPurchase = CreditDepositPurchase::where( [ 'order_id' => $orderId ] )->where( 'paid' , '>' , 0 )->get();

            // Check if the CreditDepositPurchase exists
            if ( $creditDepositPurchase->isEmpty() ) {
//                return response()->json([ 'error' => 'Credit deposit purchase not found' ] , 404);
                return response()->json( [ 'payments' => [] ] );
            }

            // Modify the date format for each payment
            $formattedPayments = $creditDepositPurchase->map( function ($payment) {
                // Format the date as Month Date, Year (e.g., January 1, 2023)
                $payment[ 'paid' ]       = AppLibrary::currencyAmountFormat( $payment[ 'paid' ] );
                $payment[ 'balance' ]    = AppLibrary::currencyAmountFormat( $payment[ 'balance' ] );
                $payment[ 'created_at' ] = date( 'M d, Y' , strtotime( $payment[ 'created_at' ] ) );
                $payment[ 'updated_at' ] = date( 'M d, Y' , strtotime( $payment[ 'updated_at' ] ) );
                return $payment;
            } );

            // Return the modified payments as JSON response
            return response()->json( [ 'payments' => $formattedPayments ] );
        }

        public function updateBalance(Request $request , Order $order)
        {
            return DB::transaction( function () use ($order , $request) {
                $change = $request->integer( 'change' );

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
                            'register_id'       => auth()->user()->openRegister()->id
                        ] );

                        PaymentMethodTransaction::create( [
                            'amount'            => $net_amount ,
                            'charge'            => 0 ,
                            'description'       => 'Order Payment #' . $order->order_serial_no ,
                            'payment_method_id' => $payment->id ,
                            'item_type'         => Order::class ,
                            'item_id'           => $order->id
                        ] );
                    }
                }
//                $balance = $order->total- $order->net_paid;

                if ( $order->balance <= 0 ) {
                    $order->update( [
                        'payment_status' => PaymentStatus::PAID ,
                        'payment_type'   => PaymentType::CASH ,
                    ] );
                }

                return new OrderResource( $order );
            } );
        }

        public function payDebt(Request $request , Order $order)
        {
            return DB::transaction( function () use ($order , $request) {
                $amount     = $request->integer( 'amount' );
                $net_amount = $amount;
                if ( $amount > 0 ) {
                    $payment = PaymentMethod::find( $request->integer( 'payment_method' ) );

                    PosPayment::create( [
                        'order_id'          => $order->id ,
                        'date'              => now() ,
                        'reference_no'      => time() ,
                        'amount'            => $net_amount ,
                        'payment_method_id' => $payment->id ,
                        'register_id'       => auth()->user()->openRegister()->id
                    ] );

                    PaymentMethodTransaction::create( [
                        'amount'            => $net_amount ,
                        'charge'            => 0 ,
                        'description'       => 'Order Payment #' . $order->order_serial_no ,
                        'payment_method_id' => $payment->id ,
                        'item_type'         => Order::class ,
                        'item_id'           => $order->id
                    ] );
                }


                if ( $order->balance <= 0 ) {
                    $order->update( [
                        'payment_status' => PaymentStatus::PAID ,
                        'payment_type'   => PaymentType::CASH ,
                    ] );
                }

                return new OrderResource( $order );
            } );
        }
    }
