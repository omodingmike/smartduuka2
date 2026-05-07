<?php

    namespace App\Models;

    use App\Enums\Status;
    use App\Enums\SubscriptionPaymentStatus;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class TenantSubscription extends Model
    {
        protected $fillable = [
            'tenant_id' , 'billing_cycle_id' , 'phone' , 'invoice_no' , 'amount' , 'status' , 'expires_at' , 'payment_status' , 'subscription_plan_id' ,
            'transaction_id'
        ];
        protected $casts    = [
            'status'         => Status::class ,
            'payment_status' => SubscriptionPaymentStatus::class ,
            'expires_at'     => 'datetime' ,
        ];

        public function billingCycle() : BelongsTo
        {
            return $this->belongsTo( BillingCycle::class );
        }

        public function subscriptionPlan() : BelongsTo
        {
            return $this->belongsTo( SubscriptionPlan::class );
        }
    }
