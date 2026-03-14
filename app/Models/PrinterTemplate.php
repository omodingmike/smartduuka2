<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterTemplate extends Model
{
    protected $fillable   = [
        'label' ,
        'value' ,
        'document_type' // FIXED: Added document_type
    ];
    public    $timestamps = FALSE;
}