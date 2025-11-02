<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class PaymentAccount extends Model
    {
        use HasFactory;

        protected $guarded = [];
        public $timestamps = false;

        public function currency() : HasOne
        {
            return $this->hasOne(Currency::class, 'id', 'currency_id');
        }
    }
