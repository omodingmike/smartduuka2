<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class StockTakeResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                'id'           => $this->id ,
                'date'         => siteDate( $this->date ) ,
                'branch'       => $this->branch ,
                'capturedBy'   => $this->capturedBy ,
                'itemsCounted' => number_format( $this->itemsCounted ) ,
                'status'       => $this->status ,
                'items'        => $this->items ,
            ];
        }
    }
