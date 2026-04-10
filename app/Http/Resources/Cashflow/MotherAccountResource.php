<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\MotherAccount;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin MotherAccount */
    class MotherAccountResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'           => $this->id ,
                'name'         => $this->name ,
                'type'         => $this->type ,
                'cash_in'      => $this->cash_in ,
                'cash_out'     => $this->cash_out ,
                'net'          => $this->cash_in - $this->cash_out ,
                'sub_accounts' => SubAccountResource::collection( $this->subAccounts )
            ];
        }
    }
