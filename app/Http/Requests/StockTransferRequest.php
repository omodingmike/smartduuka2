<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StockTransferRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'type'                     => [ 'required' , 'string' ] ,
                'source_warehouse_id'      => [ enabledWarehouse() ? 'required' : 'nullable' ] ,
                'destination_warehouse_id' => [ enabledWarehouse() ? 'required' : 'nullable' ] ,
                'products'                 => [ 'required' , 'string' ] ,
            ];
        }
    }
