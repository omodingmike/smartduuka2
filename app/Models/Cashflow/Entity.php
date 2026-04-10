<?php

    namespace App\Models\Cashflow;

    use App\Enums\CashType;
    use App\Enums\EntityType;
    use App\Enums\TransactionStatus;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class Entity extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'name' ,
            'type' ,
        ];
        protected $casts    = [ 'type' => EntityType::class ];

        public function transactions() : HasMany
        {
            return $this->hasMany( Transaction::class , 'entity_id' )
                        ->where( 'status' , TransactionStatus::CLEARED );
        }

        protected function cleared() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->transactions()->sum( 'amount' ) ,
            );
        }

        protected function balance() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->transactions()->selectRaw( '
                    SUM(CASE WHEN cash_type = ? THEN amount ELSE 0 END) -
                    SUM(CASE WHEN cash_type = ? THEN amount ELSE 0 END) as net' ,
                    [ CashType::CASH_IN->value , CashType::CASH_OUT->value ]
                )
                                  ->value( 'net' ) ?? 0 ,
            );
        }

        public function scopeWithCashSummary($query) : void
        {
            $query->withSum(
                [ 'transactions as cleared_total' => fn($q) => $q->where( 'status' , TransactionStatus::CLEARED ) ] ,
                'amount'
            )->withSum(
                [ 'transactions as cash_in_total' => fn($q) => $q->where( 'status' , TransactionStatus::CLEARED )
                                                                 ->where( 'cash_type' , CashType::CASH_IN )
                ] ,
                'amount'
            )->withSum(
                [ 'transactions as cash_out_total' => fn($q) => $q->where( 'status' , TransactionStatus::CLEARED )
                                                                  ->where( 'cash_type' , CashType::CASH_OUT )
                ] ,
                'amount'
            );
        }
    }
