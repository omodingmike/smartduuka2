<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class WastageResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                'id'        => $this->id ,
                'date'      => siteDate($this->date)  ,
                'itemCode'  => $this->itemCode ,
                'itemName'  => $this->itemName ,
                'type'      => $this->type ,
                'reason'    => $this->reason ,
                'qtyOut'    => $this->qtyOut ,
                'unitCost'  => $this->unitCost ,
                'totalCost' => currency( $this->unitCost * $this->qtyOut ) ,
                'branch'    => $this->branch ,
            ];
        }
    }
