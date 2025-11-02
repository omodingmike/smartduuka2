<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;

    class Ledger extends Model
    {
        use HasFactory;

        public    $timestamps = false;
        protected $guarded    = [];
        protected $appends    = [ 'nature' ];

        public function getNatureAttribute()
        {
            return 'ledger';
        }

        public function currency() : HasOne | Ledger | Builder
        {
            return $this->hasOne(Currency::class , 'id' , 'currency_id');
        }

        public function transactions() : HasMany
        {
            return $this->hasMany(LedgerTransaction::class , 'ledger_id' , 'id');
        }

        protected function amount() : Attribute
        {
            return Attribute::make(
                get: fn(string $value) => number_format((float) $value , 2) ,
            );
        }
    }
