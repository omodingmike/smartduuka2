<?php

    namespace App\Http\Controllers;

    use App\Enums\OrderType;
    use App\Enums\PaymentStatus;
    use App\Libraries\AppLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\Order;
    use Illuminate\Http\Request;

    class CreditDepositPurchaseController extends Controller
    {
        public function index($orderId)
        {
            // Fetch the CreditDepositPurchase based on the provided order ID
            $creditDepositPurchase = CreditDepositPurchase::where([ 'order_id' => $orderId ])->where('paid' , '>' , 0)->get();

            // Check if the CreditDepositPurchase exists
            if ( $creditDepositPurchase->isEmpty() ) {
//                return response()->json([ 'error' => 'Credit deposit purchase not found' ] , 404);
                return response()->json([ 'payments' => [] ]);
            }

            // Modify the date format for each payment
            $formattedPayments = $creditDepositPurchase->map(function ($payment) {
                // Format the date as Month Date, Year (e.g., January 1, 2023)
                $payment['paid']       = AppLibrary::currencyAmountFormat($payment['paid']);
                $payment['balance']    = AppLibrary::currencyAmountFormat($payment['balance']);
                $payment['created_at'] = date('M d, Y' , strtotime($payment['created_at']));
                $payment['updated_at'] = date('M d, Y' , strtotime($payment['updated_at']));
                return $payment;
            });

            // Return the modified payments as JSON response
            return response()->json([ 'payments' => $formattedPayments ]);
        }

        public function updateBalance(Request $request , $orderId)
        {
            $creditDepositPurchase = CreditDepositPurchase::where('order_id' , $orderId)->latest()->first();

            if ( $creditDepositPurchase?->balance <= 0 ) {
                return response()->json([ 'message' => 'Balance is already 0. No action taken.' ]);
            }

            $order       = Order::find($orderId);
            $order->paid += $request->amount;
            $order->save();
            $order->update([ 'balance' => $order->total - $request->amount ]);

            $saveCreditPurchase                    = new CreditDepositPurchase();
            $saveCreditPurchase->order_id          = $orderId;
            $saveCreditPurchase->user_id           = $creditDepositPurchase->user_id;
            $saveCreditPurchase->payment_method_id = $request->payment_method;
            $saveCreditPurchase->type              = ( $order->order_type == OrderType::CREDIT ) ? 'credit' : 'deposit';

            // Check if entered amount is greater than current balance
            if ( $request->amount >= $creditDepositPurchase->balance ) {
                $saveCreditPurchase->paid    = $creditDepositPurchase->balance;
                $saveCreditPurchase->balance = 0;
            } else {
                $saveCreditPurchase->paid    = $request->amount;
                $saveCreditPurchase->balance = $creditDepositPurchase->balance - $request->amount;
            }

            $saveCreditPurchase->save();

            if ( $saveCreditPurchase->balance <= 0 ) {
                $order->update([
                    'payment_status' => PaymentStatus::PAID ,
                    'order_type'     => OrderType::POS ,
                    'balance'        => 0 ,
                ]);
            }
            return response()->json([ 'message' => 'Balance updated successfully' ]);
        }
    }
