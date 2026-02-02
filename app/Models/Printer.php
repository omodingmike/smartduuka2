<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;

    class Printer extends Model
    {
        protected $fillable = [
            'name' ,
            'connection_type' ,
            'profile' ,
            'chars' ,
            'ip' ,
            'port' ,
            'path' ,
            'bluetooth_address' ,
            'printJobs' ,
        ];

        protected $casts = [
            'printJobs' => 'array' ,
        ];
    }
