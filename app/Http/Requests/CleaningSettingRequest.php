<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningSettingRequest extends FormRequest
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
                'order_prefix'            => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_customer_phone'     => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_service_method'     => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_item_list'          => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_order_date'         => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_business_name'      => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_business_phone'     => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_business_address'   => [ 'sometimes' , 'string' , 'max:190' ] ,
                'enable_online_bookings'  => [ 'sometimes' , 'string' , 'max:190' ] ,
                'show_service_images'     => [ 'sometimes' , 'string' , 'max:190' ] ,
                'enable_delivery_service' => [ 'sometimes' , 'string' , 'max:190' ] ,
                'welcome_message'         => [ 'sometimes' , 'string' ] ,
                'delivery_fee'            => [ 'sometimes' , 'numeric:' ] ,
                'free_delivery_threshold' => [ 'sometimes' , 'numeric:' ] ,
            ];
        }
    }
