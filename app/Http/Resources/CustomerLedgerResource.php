<?php

    namespace App\Http\Resources;

    use App\Models\CustomerLedger;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CustomerLedger */
    class CustomerLedgerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'date'        => siteDate($this->date) ,
                'reference'   => $this->reference ,
                'description' => $this->description ,
                'bill_amount' => currency( $this->bill_amount ) ,
                'paid'        => currency( $this->paid ) ,
                'balance'     => currency( $this->balance ) ,
            ];
        }
    }
