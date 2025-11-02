<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariationIngredient extends Model
{
    use HasFactory;

    protected $table = 'variation_ingredients';
    protected $fillable = ['variation_id', 'ingredient_id', 'quantity', 'buying_price', 'total'];
}
