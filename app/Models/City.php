<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class City extends Model
{
    use HasFactory,CentralConnection;

    protected $table = "cities";
    protected $fillable = ["name", "state_id", "status"];

    protected $casts = [
        'id'        => 'integer',
        'name'      => 'string',
        'state_id'  => 'integer',
        'status'    => 'integer'
    ];

    public function country() : BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(){
        return $this->belongsTo(State::class);
    }
}
