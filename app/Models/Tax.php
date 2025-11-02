<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    protected $table = "taxes";
    protected $fillable = ['name', 'code', 'tax_rate', 'status'];
    protected $casts = [
        'id'       => 'integer',
        'name'     => 'string',
        'code'     => 'string',
        'tax_rate' => 'string',
        'status'   => 'integer',
    ];

    public function productTaxes():HasMany
    {
        return $this->hasMany(ProductTax::class);
    }
}
