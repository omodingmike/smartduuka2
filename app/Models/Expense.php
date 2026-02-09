<?php

    namespace App\Models;

    use App\Enums\ExpenseType;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Spatie\MediaLibrary\HasMedia;

    class Expense extends Model implements HasMedia
    {
        use HasFactory , HasImageMedia;

        protected $fillable = [ 'name' , 'amount' , 'date' , 'category' , 'note' , 'paymentMethod' , 'referenceNo' , 'attachment' , 'recurs' , 'isRecurring' , 'user_id' , 'repetitions' , 'paid' , 'paid_on' , 'repeats_on' , 'registerMediaConversionsUsingModelInstance' , 'count' , 'register_id' ,
            'expense_category_id' ,
            'payment_method_id' ,
            'reference_no' ,
            'is_recurring' ,
            'base_amount' ,
            'extra_charge' ,
            'expense_type'
        ];
        protected $casts    = [
            'date'         => 'datetime' ,
            'paid_on'      => 'datetime' ,
            'amount'       => 'integer' ,
            'expense_type' => ExpenseType::class ,
            'paid'         => 'integer' ,
        ];
        protected $appends  = [ 'image' ];

        public function expenseCategory() : HasOne
        {
            return $this->hasOne( ExpenseCategory::class , 'id' , 'expense_category_id' );
        }

        public function registers() : HasMany | Expense
        {
            return $this->hasMany( Register::class , 'id' , 'register_id' );
        }

        public function getAttachmentAttribute($value)
        {
            return asset( 'storage/' . $value );
//            if (!empty($this->getFirstMediaUrl('attachment'))) {
//                $product = $this->getMedia('attachment')->first();
//                return $product->getUrl();
//            }
        }
    }
