<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningServiceCategoryRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'description' => [ 'nullable' ] ,
                'name'        => [ 'required' , 'unique:cleaning_service_categories,name' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
