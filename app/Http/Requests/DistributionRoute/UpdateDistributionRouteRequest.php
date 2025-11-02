<?php

    namespace App\Http\Requests\DistributionRoute;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateDistributionRouteRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'routeId'     => 'required' ,
                'sold'        => 'required' ,
                'batch'       => 'required' ,
                'destination' => 'required' ,
            ];
        }
    }
