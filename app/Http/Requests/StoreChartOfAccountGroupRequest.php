<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreChartOfAccountGroupRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'      => 'required|string|unique:chart_of_account_groups,name' ,
                'parent_id' => 'nullable|numeric' ,
                'type'      => 'required|string' ,
            ];
        }
    }
