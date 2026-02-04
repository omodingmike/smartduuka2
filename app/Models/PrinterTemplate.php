<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class PrinterTemplate extends Model
    {
        protected $fillable   = [
            'label' ,
            'value' ,
        ];
        public    $timestamps = FALSE;
    }
