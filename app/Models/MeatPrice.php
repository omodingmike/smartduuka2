<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeatPrice extends Model
{
    use HasFactory;
    protected $fillable = ['adults', 'five_to_nine', 'less_than_five'];
}
