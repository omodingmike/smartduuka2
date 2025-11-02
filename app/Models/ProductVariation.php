<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class ProductVariation extends Model implements HasMedia
{
    use HasRecursiveRelationships, InteractsWithMedia;
    protected $table = "product_variations";
    protected $fillable = [
        'product_id',
        'product_attribute_id',
        'product_attribute_option_id',
        'price',
        'sku',
        'parent_id',
        'order'
    ];

    public function getMediaUrlAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('product-variation-barcode'))) {
            return asset($this->getFirstMediaUrl('product-variation-barcode'));
        }
        return '';
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
    public function getBarcodeImageAttribute() : string
    {
        if ( ! empty($this->getFirstMediaUrl('product-barcode')) ) {
            return asset($this->getFirstMediaUrl('product-barcode'));
        }
        return '';
    }

    public function stocks(): MorphMany
    {
        return $this->morphMany(Stock::class, 'item');
    }

    public function stockItems(): MorphMany
    {
        return $this->stocks()->where('status', Status::ACTIVE);
    }
    public function otherStockItems(): MorphMany
    {
        return $this->stocks()->where('status', Status::ACTIVE);
    }
}
