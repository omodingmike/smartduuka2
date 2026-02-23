<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ExpensePaymentRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'amount'    => 'required' ,
                'method'    => 'required|not_in:null' ,
                'expenseId' => 'required' ,
            ];
        }
    }
