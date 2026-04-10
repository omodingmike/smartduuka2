<?php

    namespace App\Models\Cashflow;

    use App\Enums\Foreign;
    use Illuminate\Database\Eloquent\Model;

    class Currency extends Model
    {
        protected $fillable = [
            'name' ,
            'symbol' ,
            'foreign' ,
        ];
        protected $casts    = [ 'foreign' => Foreign::class ];
    }
