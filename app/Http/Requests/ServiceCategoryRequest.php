<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class ServiceCategoryRequest extends FormRequest
    {
        public function rules() : array
        {

            return [
                'name' => [
                    'required',
                    Rule::unique('service_categories', 'name')->ignore($this->route('serviceCategory')),
                ],
                'description' => [ 'sometimes' , 'nullable', 'string' ] ,
                'image'       => [ 'sometimes' , 'image' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
