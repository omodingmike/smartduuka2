<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningServiceRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'                         => [ 'required' ] ,
                'cleaning_service_category_id' => [ 'required'  ] ,
                'price'                        => [ 'required' , 'numeric' ] ,
                'description'                  => [ 'nullable' ] ,
                'type'                         => [ 'required' , 'integer' ] ,
                'image'                        => [ 'sometimes' , 'image' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
