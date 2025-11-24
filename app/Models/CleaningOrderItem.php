<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CleaningOrderItem extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'cleaning_service_id' ,
            'description' ,
            'quantity' ,
            'notes' ,
        ];

        public function cleaningService() : BelongsTo
        {
            return $this->belongsTo( CleaningService::class );
        }
    }
