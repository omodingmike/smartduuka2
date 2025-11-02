<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferItem extends Model
{
    use HasFactory;
    protected $table = "offer_items";
    protected $fillable = ['offer_id', 'product_id'];
    protected $casts = [
        'id'       => 'integer',
        'offer_id' => 'integer',
        'product_id'  => 'integer',
    ];

    public function offer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(offer::class, 'offer_id', 'id');
    }
    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'product_id', 'id')->withTrashed();
    }
}
