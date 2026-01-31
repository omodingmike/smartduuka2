<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;

    class Expense extends Model implements HasMedia
    {
        use HasFactory , InteractsWithMedia;

        protected $fillable = [ 'name' , 'amount' , 'date' , 'category' , 'note' , 'paymentMethod' , 'referenceNo' , 'attachment' , 'recurs' , 'isRecurring' , 'user_id' , 'repetitions' , 'paid' , 'paid_on' , 'repeats_on' , 'registerMediaConversionsUsingModelInstance' , 'count' , 'register_id' ,
            'expense_category_id' ,
            'payment_method_id' ,
            'reference_no' ,
            'is_recurring'
        ];
        protected $casts    = [
            'date'   => 'datetime' ,
            'paid_on'   => 'datetime' ,
            'amount' => 'integer' ,
            'paid'   => 'integer'
        ];

        public function expenseCategory() : HasOne
        {
            return $this->hasOne(ExpenseCategory::class , 'id' , 'category');
        }

        public function getAttachmentAttribute($value)
        {
            return asset('storage/' . $value);
//            if (!empty($this->getFirstMediaUrl('attachment'))) {
//                $product = $this->getMedia('attachment')->first();
//                return $product->getUrl();
//            }
        }
    }
