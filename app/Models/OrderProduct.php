<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderProduct extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'order_id' ,
        'item_id' ,
        'item_type' ,
        'quantity' ,
        'total' ,
        'unit_price' ,
        'quantity_picked' ,
        'product_attribute_id' ,
        'product_attribute_option_id' ,
        'variation_id' ,
        'product_id'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function productAttributeOption(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeOption::class);
    }
}
