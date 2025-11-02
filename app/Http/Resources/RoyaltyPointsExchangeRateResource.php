<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class RoyaltyPointsExchangeRateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'value'  => $this->value ?? 0 ,
                'points' => $this?->points ?? 0 ,
            ];
        }
    }
