<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\SubAccount;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin SubAccount */
    class SubAccountResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $net = $this->cash_in - $this->cash_out;

            return [
                'id'           => $this->id ,
                'name'         => $this->name ,
                'type'         => $this->type ,
                'cash_in'      => currency( $this->cash_in ) ,
                'cash_out'     => currency( $this->cash_out ) ,
                'net'          => $net ,
                'net_currency' => currency( $net ) ,
            ];
        }
    }
