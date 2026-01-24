<?php

    namespace App\Models;

    use App\Enums\MediaEnum;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Spatie\MediaLibrary\HasMedia;

    class PaymentMethod extends Model implements HasMedia
    {
        use HasFactory , HasImageMedia;

        protected $fillable = [ 'name' , 'merchant_code' , 'balance' ];
        protected $appends  = [ 'image' ];
    }
