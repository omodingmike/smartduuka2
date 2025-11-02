<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class UnitConversion extends Model
    {
        use HasFactory;

        protected $guarded = [];
        protected $casts   = [
            'base_unit_id'    => 'integer' ,
            'other_unit_id'   => 'integer' ,
            'conversion_rate' => 'decimal:2' ,
        ];
        protected $hidden  = [
            'created_at' ,
            'updated_at' ,
        ];

        public function baseUnit() : BelongsTo
        {
            return $this->belongsTo(Unit::class , 'base_unit_id');
        }

        public function otherUnit() : BelongsTo
        {
            return $this->belongsTo(Unit::class , 'other_unit_id');
        }
    }
