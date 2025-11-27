<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningServiceCustomerRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'  => [ 'required' ] ,
                'phone' => [ 'required' , 'unique:cleaning_service_customers,phone' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
