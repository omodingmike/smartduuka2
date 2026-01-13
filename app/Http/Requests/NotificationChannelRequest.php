<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'admin_email'   => ['nullable', 'email', 'max:190'],
            'admin_phone'   => ['nullable', 'string', 'max:190'],
            'events'        => ['required', 'json'],
        ];
    }
}
