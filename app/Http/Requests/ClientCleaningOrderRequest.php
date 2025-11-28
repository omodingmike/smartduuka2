<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ClientCleaningOrderRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize() : bool
        {
            return TRUE;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
         */
        public function rules() : array
        {
            return [
                'customer'       => [ 'required' ] ,
                'total'          => [ 'required' , 'numeric' ] ,
                'date'           => [ 'required' , 'date' ] ,
                'service_method' => [ 'required' , 'integer' ] ,
                'subtotal'       => [ 'required' , 'numeric' ] ,
                'tax'            => [ 'required' , 'numeric' ] ,
                'discount'       => [ 'required' , 'numeric' ] ,
                'balance'        => [ 'required' , 'numeric' ] ,
                'items'          => [ 'required' , 'string' ] ,
                'address'        => [ 'sometimes' , 'string' ] ,
                'notes'          => [ 'sometimes' , 'string' ] ,
                'image'          => [ 'sometimes' , 'file' ] ,
            ];
        }
    }
