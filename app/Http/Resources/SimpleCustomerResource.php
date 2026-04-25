<?php

    namespace App\Http\Resources;

    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin User
     */
    class SimpleCustomerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'name'           => $this->name ,
                'email'          => $this->email ?? '' ,
                'phone'          => $this->phone ?? '' ,
                'type'           => $this->type ,
                'totalSpent'     => currency( $this->total_paid ?? 0 ) ,
                'wallet_balance' => currency( $this->wallet_sum ?? 0 ) ,
                'status'         => $this->status ,
            ];
        }
    }
