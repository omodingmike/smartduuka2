<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class PaymentAccountResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'currency'    => $this->currency->symbol ?? '' ,
                'balance'     => number_format(0) ,
                'currency_id' => $this->currency_id ,
            ];
        }
    }
