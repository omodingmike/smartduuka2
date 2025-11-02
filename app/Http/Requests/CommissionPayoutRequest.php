<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CommissionPayoutRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'applies_to' => ['required', 'in:user,users,role'],
                'amount'     => ['required', 'numeric', 'min:0'],
                'user_id'    => 'nullable|integer|exists:users,id',
                'role_id'    => 'nullable|integer|exists:roles,id',
                'date'       => 'required|string',
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
