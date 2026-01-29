<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class PosPayment extends Model
    {
        use HasFactory;

        protected $fillable = [ 'date' , 'reference_no' , 'amount' , 'order_id' , 'payment_method' , 'payment_method_id' ,
            'register_id'
        ];
        protected $casts    = [
            'id'             => 'integer' ,
            'order_id'       => 'integer' ,
            'date'           => 'datetime' ,
            'reference_no'   => 'string' ,
            'amount'         => 'decimal:6' ,
            'payment_method' => 'integer'
        ];

        public function getFileAttribute()
        {
            if ( ! empty($this->getFirstMediaUrl('pos_payment')) ) {
                $product = $this->getMedia('pos_payment')->first();
                return $product->getUrl();
            }
        }

        public function paymentMethod()
        {
            return $this->belongsTo(PaymentMethod::class , 'payment_method_id');
        }
    }
