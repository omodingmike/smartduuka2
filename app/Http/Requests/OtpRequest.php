<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class OtpRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize() : bool
        {
            return TRUE;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules() : array
        {
            return [
                'delivery_method' => 'required|string' ,
                'otp_digit_limit' => 'required|numeric' ,
                'otp_expire_time' => 'required|numeric|min:1|max:60' ,
                'max_attempts'    => 'required|numeric|min:1|max:60' ,
            ];
        }
    }
