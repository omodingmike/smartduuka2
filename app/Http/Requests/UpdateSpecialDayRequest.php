<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateSpecialDayRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'        => 'required|string' ,
                'date'        => 'required|date' ,
                'status'      => 'required|string' ,
                'description' => 'nullable|string' ,
            ];
        }
    }
