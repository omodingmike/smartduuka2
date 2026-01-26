<?php

    namespace App\Models;

    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Spatie\MediaLibrary\HasMedia;

    class PaymentMethod extends Model implements HasMedia
    {
        use HasFactory , HasImageMedia;

        protected $fillable = [ 'name' , 'merchant_code' , 'balance' ];
        protected $appends  = [ 'image' , 'total_in' , 'total_out' ];

        public function transactions() : HasMany | PaymentMethod
        {
            return $this->hasMany( PaymentMethodTransaction::class , 'payment_method_id' , 'id' )->latest();
        }

        public function getBalanceAttribute() : float
        {
            return (float) $this->transactions()->sum( 'amount' );
        }

        public function getTotalInAttribute() : float
        {
            return (float) $this->transactions()->where( 'amount' , '>' , 0 )->sum( 'amount' );
        }

        public function getTotalOutAttribute() : float
        {
            return (float) $this->transactions()->where( 'amount' , '<' , 0 )->sum( 'amount' );
        }
    }
