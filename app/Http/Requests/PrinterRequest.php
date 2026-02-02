<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PrinterRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'              => [ 'required' ] ,
                'connection_type'   => [ 'required' ] ,
                'profile'           => [ 'required' ] ,
                'chars'             => [ 'nullable' ] ,
                'ip'                => [ 'nullable' ] ,
                'port'              => [ 'nullable' ] ,
                'path'              => [ 'nullable' ] ,
                'bluetooth_address' => [ 'nullable' ] ,
                'printJobs'         => [ 'nullable' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
