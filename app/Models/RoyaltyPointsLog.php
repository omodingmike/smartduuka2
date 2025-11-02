<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class RoyaltyPointsLog extends Model
    {
        use HasFactory;

        protected $guarded = [];

        public function customer() : BelongsTo
        {
            return $this->belongsTo(RoyaltyCustomer::class , 'customer_id');
        }

        public function earnedBy() : BelongsTo
        {
            return $this->belongsTo(User::class , 'earned_by');
        }

        public function redeemedBy() : BelongsTo
        {
            return $this->belongsTo(User::class , 'redeemed_by');
        }
    }
