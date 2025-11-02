<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class LedgerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'code'        => $this->code ,
                'name'        => $this->name ,
                'parent_id'   => $this->parent_id ,
                'currency_id' => $this->currency_id ,
                'amount'      => number_format((float) $this->amount , 2) ,
                'type'        => $this->type ,
                'nature'      => 'ledger' ,
            ];
        }
    }
