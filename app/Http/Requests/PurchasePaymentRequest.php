<?php

    namespace App\Http\Requests;

    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use Illuminate\Foundation\Http\FormRequest;

    class PurchasePaymentRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize() : bool
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
         */
        public function rules() : array
        {
            return [
                'purchase_id'    => [ 'required' , 'not_in:0' , 'not_in:null' ] ,
                'date'           => [ 'required' , 'string' ] ,
                'reference_no'   => [ 'nullable' , 'string' ] ,
                'amount'         => [ 'required' , 'numeric' ] ,
                'purchase_type'  => [ 'sometimes' , 'numeric' ] ,
                'payment_method' => [ 'required' , 'not_in:0' , 'not_in:null' ] ,
                'file'           => [ 'nullable' , 'file' , 'mimes:jpg,jpeg,png,pdf' , 'max:2048' ] ,
            ];
        }
    }
