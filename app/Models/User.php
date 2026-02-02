<?php

    namespace App\Models;

    use App\Enums\Status;
    use App\Services\CommissionCalculator;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasManyThrough;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;
    use Spatie\Permission\Models\Role;
    use Spatie\Permission\Traits\HasRoles;

    class User extends Authenticatable implements HasMedia
    {
        use InteractsWithMedia;
        use HasApiTokens;
        use HasFactory;
        use HasRoles;
        use Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $table   = "users";
        protected $appends = [ 'credits' , 'sales','register', 'total_revenue', 'average_order_value' ];

        protected $fillable = [
            'name' ,
            'email' ,
            'password' ,
            'username' ,
            'phone' ,
            'country_code' ,
            'is_guest' ,
            'status' ,
            'email_verified_at' ,
            'commission' ,
            'branch_id' ,
            'phone2' ,
            'type' ,
            'notes' ,
            'pin',
            'last_login_date',
            'department',
            'force_reset'
        ];

        /**
         * The attributes that should be hidden for serialization.
         *
         * @var array<int, string>
         */
        protected $hidden = [
            'password' ,
            'remember_token' ,
        ];

        /**
         * The attributes that should be cast.
         *
         * @var array<string, string>
         */

        protected $casts = [
            'id'                => 'integer' ,
            'name'              => 'string' ,
            'email'             => 'string' ,
            'password'          => 'hashed' ,
            'username'          => 'string' ,
            'phone'             => 'string' ,
            'country_code'      => 'string' ,
            'is_guest'          => 'integer' ,
            'status'            => Status::class ,
            'email_verified_at' => 'datetime' ,
            'credits'           => 'decimal' ,
            'last_login_date'   => 'datetime',
            'force_reset'       => 'boolean',
        ];

        public function guardName() : string
        {
            return 'sanctum';
        }

        protected function register() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->openRegister() ,
            );
        }

        public function registers() : User | HasMany
        {
            return $this->hasMany( Register::class , 'user_id' , 'id' );
        }

        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'profile' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'profile' ) );
            }
            return asset( 'images/required/profile.png' );
        }

        public function openRegister()
        {
            return $this->registers()->whereNull( 'closed_at' )->latest()->first();
        }

        public function commissions() : HasManyThrough | Builder | User
        {
            return $this->hasManyThrough( Commission::class , CommissionTarget::class , 'user_id' , 'id' , 'id' , 'commission_id' );
        }

        public function commissionTargets() : HasMany | Builder | User
        {
            return $this->hasMany( CommissionTarget::class );
        }

        public function payouts()
        {
            return $this->hasMany( CommissionPayout::class , 'user_id' , 'id' );
        }


        public function getThumbAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'profile' ) ) ) {
                $profile = $this->getMedia( 'profile' )->last();
                return $profile->getUrl( 'thumb' );
            }
            return asset( 'images/required/profile.png' );
        }

        public function registerMediaConversions(Media $media = NULL) : void
        {
            $this->addMediaConversion( 'thumb' )->crop( 'crop-center' , 225 , 225 )->keepOriginalImageFormat()->sharpen( 10 );
        }

        public function orders() : HasMany
        {
            return $this->hasMany( Order::class , 'user_id' , 'id' );
        }

        public function stocks() : HasMany
        {
//            return $this->hasMany( Stock::class , 'user_id' , 'id' )->where( 'quantity' , '>' , 0 );
            return $this->hasMany( Stock::class , 'user_id' , 'id' );
        }

        protected function sales() : Attribute
        {
            return Attribute::make(
                get: function () {
                    $calculator = resolve( CommissionCalculator::class );
                    return $this->stocks()
                                ->where( 'sold' , '>' , 0 )
                                ->get()
                                ->sum( function (Stock $stock) use ($calculator) {
                                    return $calculator->calculateTotalSales( $stock );
                                } );
                }
            );
        }

        public function payments() : HasMany
        {
            return $this->hasMany( CustomerPayment::class , 'user_id' , 'id' );
        }

        protected function credits() : Attribute
        {
            return Attribute::make( get: fn() => $this->orders()->sum( 'balance' ) );
        }

        public function addresses() : HasMany
        {
            return $this->hasMany( Address::class );
        }


        public function getMyRoleAttribute()
        {
            return $this->roles->pluck( 'id' , 'id' )->first();
        }

        public function getrole() : HasOne
        {
            return $this->hasOne( Role::class , 'id' , 'myrole' );
        }

        protected function totalRevenue(): Attribute
        {
            return Attribute::make(
                get: fn () => $this->orders()->sum('total')
            );
        }

        protected function averageOrderValue(): Attribute
        {
            return Attribute::make(
                get: fn () => $this->orders()->avg('total') ?? 0
            );
        }

    }
