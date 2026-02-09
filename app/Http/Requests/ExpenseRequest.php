<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'date'          => 'required|date',
            'category'      => 'required|integer',
            'baseAmount'    => 'required|numeric',
            'extraCharge'   => 'nullable|numeric',
            'amount'        => 'required|numeric',
            'paidAmount'    => 'nullable|numeric',
            'isRecurring'   => 'nullable|boolean',
            'note'          => 'nullable|string',
            'image'         => 'nullable|image',
        ];
    }
}
