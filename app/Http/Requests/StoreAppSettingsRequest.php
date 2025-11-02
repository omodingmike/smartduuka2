<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreAppSettingsRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'a4_receipt'     => [ 'required' , 'numeric' ] ,
                'primaryColor'   => [ 'required' , 'string' ] ,
                'primaryLight'   => [ 'required' , 'string' ] ,
                'secondaryColor' => [ 'required' , 'string' ] ,
                'secondaryLight' => [ 'required' , 'string' ] ,
            ];
        }
    }
