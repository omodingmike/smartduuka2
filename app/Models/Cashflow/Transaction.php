<?php

    namespace App\Models\Cashflow;

    use App\Enums\CashType;
    use App\Enums\TransactionStatus;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\HasMedia;

    class Transaction extends Model implements HasMedia
    {
        use SoftDeletes , HasImageMedia;

        protected $fillable = [
            'date' ,
            'reference' ,
            'cash_type' ,
            'entity_id' ,
            'amount' ,
            'fee' ,
            'currency_id' ,
            'accountable_id' ,
            'accountable_type' ,
            'description' , 'cash_in' , 'cash_out' ,
            'status' , 'transaction_category_id' , 'exchange_rate' ,
        ];

        public function accountable() : MorphTo
        {
            return $this->morphTo();
        }

        public function entity() : BelongsTo
        {
            return $this->belongsTo( Entity::class );
        }

        public function currency() : BelongsTo
        {
            return $this->belongsTo( Currency::class );
        }

        public function transactionCategory() : BelongsTo
        {
            return $this->belongsTo( TransactionCategory::class );
        }

        protected function casts() : array
        {
            return [
                'date'      => 'datetime' ,
                'cash_type' => CashType::class ,
                'status'    => TransactionStatus::class ,
            ];
        }
    }
