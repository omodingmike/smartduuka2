<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningOrderItemRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'cleaning_service_id' => [ 'required' , 'exists:cleaning_services' ] ,
                'description'         => [ 'required' ] ,
                'quantity'            => [ 'required' , 'integer' ] ,
                'notes'               => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
