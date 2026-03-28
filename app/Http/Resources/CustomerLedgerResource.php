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
                'date'        => $this->date ,
                'reference'   => $this->reference ,
                'description' => $this->description ,
                'bill_amount' => $this->bill_amount ,
                'paid'        => $this->paid ,
                'balance'     => $this->balance ,
                'created_at'  => $this->created_at ,
                'updated_at'  => $this->updated_at ,
            ];
        }
    }
