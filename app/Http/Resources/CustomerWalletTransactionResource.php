<?php

    namespace App\Http\Resources;

    use App\Models\CustomerWalletTransaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CustomerWalletTransaction */
    class CustomerWalletTransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                => $this->id ,
                'amount'            => currency( $this->amount ) ,
                'reference'         => $this->reference ,
                'type'              => $this->type ,
                'balance'           => currency( $this->balance ) ,
                'created_at'        => datetime( $this->created_at ) ,
                'user_id'           => $this->user_id ,
                'payment_method_id' => $this->payment_method_id ,
                'user'              => new UserResource( $this->whenLoaded( 'user' ) ) ,
            ];
        }
    }
