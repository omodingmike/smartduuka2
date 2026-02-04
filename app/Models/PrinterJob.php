<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

    class PrinterJob extends Model
    {
        public $timestamps = FALSE;

        protected $fillable = [
            'printer_id' ,
            'printer_template_id' ,
        ];

        public function printer() : BelongsToMany
        {
            return $this->belongsToMany( Printer::class );
        }

        public function printerTemplate() : BelongsTo
        {
            return $this->belongsTo( PrinterTemplate::class );
        }
    }
