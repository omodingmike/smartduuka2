<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class LedgerTransaction extends Model
    {
        use HasFactory;

        protected $guarded = [];
        protected $casts   = [ 'balance' => 'float' , 'credit' => 'float' , 'debit' => 'float' ];

        public function ledger() : BelongsTo
        {
            return $this->belongsTo(Ledger::class , 'ledger_id' , 'id');
        }
    }
