<?php

    namespace App\Http\Resources;

    use App\Models\TenantSubscription;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin TenantSubscription */
    class TenantSubscriptionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'duration'   => $this->duration ,
                'plan'       => $this->plan ,
                'setup'      => $this->setup ,
                'amount'     => $this->amount ,
                'created_at' => $this->created_at ,
                'status'     => $this->status ,
                'expires_at' => $this->expires_at ,
            ];
        }
    }
