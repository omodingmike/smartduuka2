<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class ActivityLog extends Model
    {
        use HasFactory;

        protected $guarded = [];

        public function user() : MorphTo
        {
            return $this->morphTo('user')->withTrashed();
        }
    }
