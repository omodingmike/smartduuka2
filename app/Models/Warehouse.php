<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Warehouse extends Model
    {
        use HasFactory;

        protected $fillable = [
            'name' ,
            'deletable' ,
            'email' ,
            'location' ,
            'phone' ,
            'manager',
            'capacity',
            'status'
        ];
        protected $casts    = [
            'deletable' => 'boolean'
        ];
    }
