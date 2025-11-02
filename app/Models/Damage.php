<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\morphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Damage extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'date',
        'reference_no',
        'subtotal',
        'tax',
        'discount',
        'total',
        'note'
    ];

    protected $casts = [
        'id'        => 'integer',
        'date'      => 'datetime',
        'subtotal'  => 'decimal:6',
        'tax'       => 'decimal:6',
        'discount'  => 'decimal:6',
        'total'     => 'integer',
        'note'      => 'string'
    ];

    public function stocks(): morphMany
    {
        return $this->morphMany(Stock::class, 'model');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function getFileAttribute()
    {
        if (!empty($this->getFirstMediaUrl('damage'))) {
            $product = $this->getMedia('damage')->first();
            return $product->getUrl();
        }
    }
}
