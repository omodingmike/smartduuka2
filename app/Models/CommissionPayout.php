<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Spatie\Permission\Models\Role;

    class CommissionPayout extends Model
    {
        protected $fillable = [
            'applies_to' ,
            'amount' ,
            'user_id' ,
            'role_id' ,
            'date' ,
            'reference' ,
        ];

        protected $casts = [
            'date' => 'datetime' ,
        ];

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class );
        }

        public function role() : BelongsTo
        {
            return $this->belongsTo( Role::class );
        }
    }
