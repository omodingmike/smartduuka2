<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize() : bool
    {
        return true;
    }


    public function rules() : array
    {
        return [
            'name'                  => ['required', 'string', 'max:190'],
            'email'                 => [
                'required',
                'email',
                'max:190',
                Rule::unique("users", "email")->ignore($this->route('employee.id'))
            ],
            'password'              => [
                $this->route('employee.id') ? 'nullable' : 'required',
                'string',
                'min:6',
                'confirmed'
            ],
            'password_confirmation' => [$this->route('employee.id') ? 'nullable' : 'required', 'string', 'min:6'],
            'username'              => [
                'nullable',
                'max:190',
                Rule::unique("users", "username")->ignore($this->route('employee.id'))
            ],
            'device_token'          => ['nullable', 'string'],
            'web_token'             => ['nullable', 'string'],
            'phone'                 => [
                'nullable',
                'string',
                'max:20',
                Rule::unique("users", "phone")->ignore($this->route('employee.id'))
            ],
            'status'                => ['required', 'numeric', 'max:24'],
            'role_id'               => ['required', 'numeric'],
        ];
    }
}
