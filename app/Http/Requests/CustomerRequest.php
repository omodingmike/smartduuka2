<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name'                  => ['required', 'string', 'max:190'],
            'email'                 => [
                'sometimes',
                'email',
                'max:190',
                Rule::unique("users", "email")->ignore($this->route('customer.id'))
            ],

            'username'              => [
                'nullable',
                'max:190',
                Rule::unique("users", "username")->ignore($this->route('customer.id'))
            ],
            'device_token'          => ['nullable', 'string'],
            'web_token'             => ['nullable', 'string'],
//            'phone'                 => [
//                'required',
//                'string',
//                'max:20',
//                Rule::unique("users", "phone")->ignore($this->route('customer.id'))
//            ],
            'status'                => ['required', 'numeric', 'max:24'],
            'country_code'          => ['required', 'string', 'max:20'],
        ];
    }
}
