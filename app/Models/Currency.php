<?php

    namespace App\Models;

    use App\Enums\Foreign;
    use Illuminate\Database\Eloquent\Model;

    class Currency extends Model
    {
        protected $table    = "currencies";
        protected $fillable = [ 'name' , 'symbol' , 'code' , 'is_cryptocurrency' , 'exchange_rate' ,
            'is_base'
        ];

        protected $casts = [
            'id'                => 'integer' ,
            'name'              => 'string' ,
            'symbol'            => 'string' ,
            'code'              => 'string' ,
            'is_cryptocurrency' => 'integer' ,
            'exchange_rate'     => 'decimal:6' ,
            'foreign'           => Foreign::class
        ];
    }
