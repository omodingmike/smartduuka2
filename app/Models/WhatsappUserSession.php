<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class WhatsappUserSession extends Model
    {
        protected $guarded = [];
        protected $casts   = [
            'data' => 'array' ,
        ];
    }
