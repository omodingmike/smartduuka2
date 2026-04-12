<?php

    namespace App\Models\Cashflow;

    use App\Enums\SubAccountType;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class SubAccount extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'name' ,
            'type' ,
            'mother_account_id' ,
        ];
        protected $casts    = [
            'type' => SubAccountType::class
        ];

        public function transactions() : MorphMany
        {
            return $this->morphMany( Transaction::class , 'accountable' );
        }
    }
