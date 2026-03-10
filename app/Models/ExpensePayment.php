<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class ExpensePayment extends Model
    {
        use HasFactory;

        protected $fillable = [ 'user_id' , 'expense_id' , 'date' , 'referenceNo' , 'amount' , 'paymentMethod' , 'attachment' , 'register_id' , 'payment_method_id'
        ];

        protected $casts = [
            'date' => 'datetime'
        ];

        public function method() : BelongsTo
        {
            return $this->belongsTo( PaymentMethod::class , 'payment_method_id' , 'id' );
        }

        public function expense() : BelongsTo
        {
            return $this->belongsTo( Expense::class , 'expense_id' , 'id' );
        }
    }
