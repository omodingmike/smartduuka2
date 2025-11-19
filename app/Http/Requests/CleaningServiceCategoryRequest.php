<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningServiceCategoryRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'        => [ 'required' ] ,
                'description' => [ 'nullable' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
