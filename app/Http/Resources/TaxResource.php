<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class TaxResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                "id"       => $this->id ,
                "name"     => $this->name ,
                "code"     => $this->code ,
                "tax_rate" => (float) $this->tax_rate ,
                "status"   => $this->status ,
            ];
        }
    }
