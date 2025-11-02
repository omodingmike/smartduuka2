<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class IngredientRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'           => [ 'required' , 'string' , 'max:255' ] ,
                'buying_price'   => [ 'required' , 'numeric' , 'min:0' ] ,
                'unit'           => [ 'required' , 'string' , 'regex:/^[a-zA-Z]+$/' , 'max:255' ] ,
//            'quantity'        => ['required', 'integer', 'min:0'],
                'quantity_alert' => [ 'required' , 'numeric' , 'min:0' ] ,
            ];
        }
    }
