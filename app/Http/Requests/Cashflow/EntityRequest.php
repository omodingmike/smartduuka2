<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class EntityRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name' => [ 'required' ] ,
                'type' => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
