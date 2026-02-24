<?php

    namespace App\Models;

    use App\Enums\ExpenseType;
    use App\Enums\MediaEnum;
    use App\Enums\PaymentStatus;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;

    class Expense extends Model implements HasMedia
    {
        use HasFactory , InteractsWithMedia;

        protected $fillable = [ 'name' , 'amount' , 'date' , 'note' , 'attachment' , 'recurs' , 'repetitions' , 'paid' , 'paid_on' , 'repeats_on' , 'count' , 'register_id' , 'expense_category_id' , 'reference_no' , 'is_recurring' , 'base_amount' , 'extra_charge' , 'expense_type' , 'expense_id' , 'registerMediaConversionsUsingModelInstance'
        ];
        protected $casts    = [
            'date'         => 'datetime' ,
            'paid_on'      => 'datetime' ,
            'amount'       => 'integer' ,
            'expense_type' => ExpenseType::class ,
            'paid'         => 'integer' ,
        ];
        protected $appends  = [ 'image' , 'balance' , 'payment_status' ];

        public function expenseCategory() : HasOne
        {
            return $this->hasOne( ExpenseCategory::class , 'id' , 'expense_category_id' );
        }

        public function registers() : HasMany | Expense
        {
            return $this->hasMany( Register::class , 'id' , 'register_id' );
        }

        public function payments() : HasMany | Expense
        {
            return $this->hasMany( ExpensePayment::class );
        }

        public function getAttachmentAttribute($value)
        {
            return asset( 'storage/' . $value );
//            if (!empty($this->getFirstMediaUrl('attachment'))) {
//                $product = $this->getMedia('attachment')->first();
//                return $product->getUrl();
//            }
        }

        protected function getMediaCollection() : string
        {
            return MediaEnum::IMAGES_COLLECTION;
        }

        public function getImageAttribute() : ?string
        {
            if ( $this->getMediaCollection() && ! empty( $this->getLastMediaUrl( $this->getMediaCollection() ) ) ) {
                return asset( $this->getLastMediaUrl( $this->getMediaCollection() ) );
            }
            return NULL;
        }

        public function getBalanceAttribute()
        {
            return $this->amount - $this->paid;
        }

        public function getPaymentStatusAttribute() : PaymentStatus
        {
            if ( $this->paid >= $this->amount ) {
                return PaymentStatus::PAID;
            }
            elseif ( $this->paid > 0 ) {
                return PaymentStatus::PARTIALLY_PAID;
            }
            else {
                return PaymentStatus::UNPAID;
            }
        }
    }
