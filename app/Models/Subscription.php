<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class Subscription extends Model
    {
        use HasFactory;

        protected $fillable   = [ 'invoice_no' , 'phone' , 'starts_at' , 'status' , 'amount' , 'project_id' , 'expires_at' , 'external_id' , 'vendor_transaction_id' , 'payment_status' , 'vendor_message' , 'business_id' , 'plan_id' ];
        protected $casts      = [
            'expires_at' => 'datetime' ,
            'created_at' => 'datetime' ,
            'updated_at' => 'datetime' ,
            'starts_at'  => 'datetime' ,
        ];
        protected $connection = 'pgsql2';

        public function plan() : BelongsTo
        {
            return $this->belongsTo(SubscriptionPlan::class , 'plan_id' , 'id');
        }
    }
