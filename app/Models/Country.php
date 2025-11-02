<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\HasMany;

    class Country extends Model
    {
        use HasFactory;

        protected $table = "countries";

        protected $fillable = [ "name" , "code" , "status" ];

        protected $casts = [
            'id'     => 'integer' ,
            'name'   => 'string' ,
            'code'   => 'string' ,
            'status' => 'integer'
        ];

        public function states() : Country | Builder | HasMany
        {
            return $this->hasMany(State::class , 'country_id' , 'id');
        }
    }
