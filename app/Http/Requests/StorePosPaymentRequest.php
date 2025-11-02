<?php

    namespace App\Http\Requests;

    use App\Models\Order;
    use App\Models\PosPayment;
    use Illuminate\Foundation\Http\FormRequest;

    class StorePosPaymentRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'purchase_id'    => [ 'required' , 'not_in:0' , 'not_in:null' ] ,
//            'date'           => ['required', 'string'],
                'reference_no'   => [ 'nullable' , 'string' ] ,
                'amount'         => [ 'required' , 'numeric' ] ,
                'paid'           => [ 'sometimes' , 'numeric' ] ,
                'points'         => [ 'sometimes' , 'numeric' ] ,
                'change'         => [ 'sometimes' , 'numeric' ] ,
                'payment_method' => [ 'required' , 'not_in:0' , 'not_in:null' ] ,
                'file'           => [ 'nullable' , 'file' , 'mimes:jpg,jpeg,png,pdf' , 'max:2048' ] ,
            ];
        }

        public function withValidator($validator)
        {
            $validator->after(function ($validator) {
                $status  = false;
                $message = '';

                $purchasePaymentAmount = PosPayment::where('order_id' , request('purchase_id'))->sum('amount');
                $order                 = Order::findOrFail(request('purchase_id'));

                $paymentDue = (float) $order->total - (float) $purchasePaymentAmount;

                if ( $paymentDue < request('amount') ) {
                    $status  = true;
                    $message = trans('all.message.price_total_invalid');
                }

                if ( $status ) {
                    $validator->errors()->add('global' , $message);
                }
            });
        }
    }
