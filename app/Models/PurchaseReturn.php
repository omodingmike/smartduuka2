<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class PurchaseReturn extends Model
    {
        protected $fillable = [
            'supplier_id' ,
            'purchase_id' ,
            'date' ,
            'debit_note' ,
            'notes' ,
        ];

        public function supplier() : BelongsTo
        {
            return $this->belongsTo( Supplier::class );
        }

        public function purchase() : BelongsTo
        {
            return $this->belongsTo( Purchase::class );
        }

        protected function casts() : array
        {
            return [
                'date' => 'datetime' ,
            ];
        }
    }
