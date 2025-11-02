<?php

    namespace App\Http\Requests;

    use App\Enums\Modules;
    use Illuminate\Foundation\Http\FormRequest;

    class StoreModuleRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'module_warehouse'    => [ 'required' , 'numeric' ] ,
                'module_wholesale'    => [ 'required' , 'numeric' ] ,
                'accounting'          => [ 'required' , 'numeric' ] ,
                'production'          => [ 'required' , 'numeric' ] ,
                Modules::COMMISSION   => [ 'required' , 'numeric' ] ,
                Modules::DISTRIBUTION => [ 'required' , 'numeric' ] ,
            ];
        }
    }
