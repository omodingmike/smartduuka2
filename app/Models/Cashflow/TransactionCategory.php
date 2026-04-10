<?php

namespace App\Models\Cashflow;

use App\Enums\CashType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionCategory extends Model
{
    protected $fillable = [
        'name',
        'cash_type',
    ];
    protected $casts = ['cash_type' => CashType::class];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transaction_category_id');
    }

    protected function usage(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->transactions()->count(),
        );
    }

    protected function totalVolume(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->transactions()->sum('amount'),
        );
    }
}
