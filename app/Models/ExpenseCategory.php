<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class ExpenseCategory extends Model
{
    use HasFactory,HasRecursiveRelationships;

    public $timestamps = false;
    protected $fillable = ['name', 'user_id', 'parent_id', 'status','description'];

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }
}
