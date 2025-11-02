<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateChartOfAccountGroupRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            $id = $this->route('ledger_group');
            return [
                'name'      => [
                    'required' ,
                    'string' ,
                    Rule::unique('chart_of_account_groups' , 'name')->ignore($id) ,
                ] ,
                'parent_id' => 'nullable|numeric' ,
                'type'      => 'required|string' ,
            ];
        }
    }
