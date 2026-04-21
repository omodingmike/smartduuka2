<?php

    namespace App\Models;

    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Model;
    use Spatie\MediaLibrary\HasMedia;

    class ServiceCategory extends Model implements HasMedia
    {
        use HasImageMedia;

        protected $fillable = [
            'name' ,
            'description' ,
        ];
    }
