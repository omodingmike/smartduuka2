<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Supplier extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = "suppliers";
    protected $fillable = ['company', 'name', 'email', 'country_code', 'phone', 'address', 'country', 'state', 'city', 'postal_code'];
    protected $casts = [
        'id'           => 'integer',
        'company'      => 'string',
        'name'         => 'string',
        'email'        => 'string',
        'country_code' => 'string',
        'phone'        => 'string',
        'address'      => 'string',
        'country'      => 'string',
        'state'        => 'string',
        'city'         => 'string',
        'postal_code'  => 'string',
    ];

    public function getImageAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('supplier'))) {
            return asset($this->getFirstMediaUrl('supplier'));
        }
        return asset('images/required/profile.png');
    }

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $this->hasMany(Purchases::class, 'supplier_id', 'id');
    }
}
