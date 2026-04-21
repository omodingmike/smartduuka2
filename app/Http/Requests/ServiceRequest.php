<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ServiceRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'                => [ 'required' ] ,
                'service_category_id' => [ 'required' , 'exists:service_categories,id' ] ,
                'base_price'          => [ 'required' , 'numeric:' ] ,
                'duration'            => [ 'sometimes' , 'string' ] ,
                'description'         => [ 'sometimes' , 'string' ] ,
                'type'                => [ 'required' , 'numeric:' ] ,
                'status'              => [ 'required' , 'numeric:' ] ,
                'stockConsumption'    => [ 'required' , 'string:' ] ,
                'addons'              => [ 'required' , 'string:' ] ,
                'tiers'               => [ 'required' , 'string:' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
