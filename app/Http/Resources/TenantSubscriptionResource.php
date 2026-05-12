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
                'id'               => $this->id ,
                'tenant_id'        => $this->tenant_id ,
                'billing_cycle_id' => $this->billing_cycle_id ,
                'phone'            => $this->phone ,
                'invoice_no'       => $this->invoice_no ,
                'transaction_id'   => $this->transaction_id ,
                'amount'           => $this->amount ,
                'amount_currency'  => 'UGX ' . number_format( $this->amount ) ,
                'status'           => $this->status ,
                'payment_status'   => $this->payment_status ,
                'expires_at'       => $this->expires_at->format( 'd-M-Y H:i:s' ) ,
                'created_at'       => $this->created_at->format( 'd-M-Y H:i:s' ) ,
                'updated_at'       => $this->updated_at ,
                'billingCycle'     => new BillingCycleResource( $this->whenLoaded( 'billingCycle' ) ) ,
                'subscriptionPlan' => new SubscriptionPlanResource( $this->whenLoaded( 'subscriptionPlan' ) ) ,
            ];
        }
    }
