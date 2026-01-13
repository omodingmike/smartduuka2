<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsGatewayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'sms_gateway' => ['required', 'string'],
            'at_username' => ['nullable', 'string'],
            'at_apikey'   => ['nullable', 'string'],
            'status'      => ['nullable', 'string'],
        ];
    }
}
