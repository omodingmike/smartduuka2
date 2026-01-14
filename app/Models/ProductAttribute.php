<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $table = "product_attributes";
    protected $fillable = ['name','status'];
    protected $casts = [
        'id'     => 'integer',
        'name'   => 'string',
    ];

    public function productAttributeOptions(): HasMany
    {
        return $this->hasMany(ProductAttributeOption::class, 'product_attribute_id', 'id');
    }
}
