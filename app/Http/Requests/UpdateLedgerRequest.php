<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateLedgerRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'code'        => [
                    'required' ,
                    Rule::unique('ledgers')->ignore($this->id) ,
                ] ,
                'name'        => [
                    'required' ,
                    Rule::unique('ledgers')
                        ->where(function ($query) {
                            return $query->where('parent_id' , $this->input('parent_id'));
                        })
                        ->ignore($this->id) ,
                ] ,
                'parent_id'   => 'required|numeric|exists:chart_of_account_groups,id' ,
                'currency_id' => 'required|numeric|exists:currencies,id' ,
                'type'        => 'required|string' ,
            ];
        }
    }
