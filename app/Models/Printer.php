<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        ];

        public function templates() : BelongsToMany
        {
            return $this->belongsToMany( PrinterTemplate::class , 'printer_jobs' , 'printer_id' , 'printer_template_id' );
        }
    }
