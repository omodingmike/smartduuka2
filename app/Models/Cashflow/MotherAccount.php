<?php

    namespace App\Models\Cashflow;

    use App\Enums\CashType;
    use App\Enums\SubAccountType;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class MotherAccount extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'name' ,
            'type' , 'cash_in' , 'cash_out'
        ];
        protected $casts    = [
            'type' => SubAccountType::class
        ];

        public function transactions() : MorphMany
        {
            return $this->morphMany( Transaction::class , 'accountable' );
        }

        public function subAccounts() : HasMany
        {
            return $this->hasMany( SubAccount::class );
        }

//        protected function cashIn() : Attribute
//        {
//            return Attribute::make(
//                get: fn() => $this->transactions()->whereCashType( CashType::CASH_IN )->sum( 'amount' ) ,
//            );
//        }
//
//        protected function cashOut() : Attribute
//        {
//            return Attribute::make(
//                get: fn() => $this->transactions()->whereCashType( CashType::CASH_OUT )->sum( 'amount' ) ,
//            );
//        }
    }
