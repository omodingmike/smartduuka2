<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class UnitConversionResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                'id'              => $this->id ,
                'base_unit'       => new UnitResource($this->whenLoaded('baseUnit')) ,
                'other_unit'      => new UnitResource($this->whenLoaded('otherUnit')) ,
                'conversion_rate' => $this->conversion_rate ,
            ];
        }
    }
