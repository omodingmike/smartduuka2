<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemExtra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "item_extras";
    protected $fillable = ['product_id', 'name', 'status', 'price'];
    protected $casts = [
        'id'      => 'integer',
        'product_id' => 'integer',
        'name'    => 'string',
        'status'  => Status::class,
        'price'   => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'product_id', 'id');
    }
}
