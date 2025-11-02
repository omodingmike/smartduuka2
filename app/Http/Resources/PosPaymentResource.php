<?php

    namespace App\Http\Resources;

    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class PosPaymentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $purchasePaymentAmount = floatval(PosPayment::where('order_id' , $this->id)->sum('amount'));
            $due_payment           = (float) $this->total - $purchasePaymentAmount;
            return [
                'due_payment'     => $due_payment ,
                'required_points' => $due_payment / royaltyPointsExchangeRate() ,
                'payment_methods' => PaymentMethod::all() ,
                'order'           => new OrderResource(Order::find($this->id)) ,
            ];
        }
    }
