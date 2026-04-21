<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class BookingRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'customer_name'  => [ 'required' ] ,
                'customer_phone' => [ 'required' ] ,
                'service_id'     => [ 'required' , 'exists:services,id' ] ,
                'date'           => [ 'required' , 'date' ] ,
                'status'         => [ 'required' , 'integer' ] ,
                'total'          => [ 'required' , 'numeric:' ] ,
                'notes'          => [ 'required' ] ,
                'adds_on'        => [ 'sometimes' , 'string' ] ,
                'activity_logs'  => [ 'sometimes' , 'string' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
