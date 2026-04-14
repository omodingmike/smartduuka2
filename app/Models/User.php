<?php

    namespace App\Models;

    use App\Enums\CustomerPaymentType;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentType;
    use App\Enums\Status;
    use Carbon\Carbon;
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
    use Stancl\Tenancy\Contracts\Syncable;
    use Stancl\Tenancy\Database\Concerns\ResourceSyncing;

    class User extends Authenticatable implements HasMedia , Syncable
    {
        use InteractsWithMedia;
        use HasApiTokens;
        use HasFactory;
        use HasRoles;
        use Notifiable , ResourceSyncing;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $table = "users";

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
            'pin' ,
            'last_login_date' ,
            'department' , 'device_token' , 'web_token' , 'balance' , 'remember_token' , 'creator_type' , 'creator_id' , 'editor_type' , 'editor_id' , 'commission_paid' , 'two_factor_secret' , 'two_factor_recovery_codes' , 'two_factor_confirmed_at' , 'average_order_value' , 'credit_orders' , 'credits' , 'sales' , 'total_revenue' , 'registerMediaConversionsUsingModelInstance' ,
            'force_reset' ,
            'raw_pin' , 'oldest_credit_order' , 'total_credit_orders' , 'wallet' , 'tenant_id' ,
            'is_reset' , 'global_id'
        ];

        /**
         * The attributes that should be hidden for serialization.
         *
         * @var array<int, string>
         */
        protected $hidden = [
            'password' ,
            'remember_token' , 'pin'
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
            'last_login_date'   => 'datetime' ,
            'force_reset'       => 'boolean' ,
            'is_reset'          => 'boolean' ,
        ];


        public function getGlobalIdentifierKey()
        {
            return $this->getAttribute( $this->getGlobalIdentifierKeyName() );
        }

        public function getGlobalIdentifierKeyName() : string
        {
            return 'global_id';
        }

        public function getCentralModelName() : string
        {
            return CentralUser::class;
        }

        public function getSyncedAttributeNames() : array
        {
            return [
                'id' ,
                'name' ,
                'password' ,
                'email' ,
            ];
        }

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

        protected function name() : Attribute
        {
            return Attribute::make(
                get: fn(string $name) => ucwords( $name ) ,
            );
        }

        public function registers() : HasMany
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

        public function openRegister() : User | Register | \stdClass | null
        {
            return $this->registers()->whereNull( 'closed_at' )->latest()->first();
        }

        public function commissions() : HasManyThrough
        {
            return $this->hasManyThrough( Commission::class , CommissionTarget::class , 'user_id' , 'id' , 'id' , 'commission_id' );
        }

        public function commissionTargets() : HasMany
        {
            return $this->hasMany( CommissionTarget::class );
        }

        public function payouts() : HasMany
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
            return $this->hasMany( Order::class , 'user_id' , 'id' )->where( 'status' , '<>' , OrderStatus::CANCELED );
        }

        public function stocks() : HasMany
        {
            return $this->hasMany( Stock::class , 'user_id' , 'id' );
        }

        public function payments() : HasMany
        {
            return $this->hasMany( CustomerPayment::class , 'user_id' , 'id' );
        }

        public function debtPayments() : HasMany
        {
            return $this->hasMany( CustomerPayment::class , 'user_id' , 'id' )
                        ->where( 'customer_payment_type' , CustomerPaymentType::DEBT );
        }

        public function legacyDebts() : HasMany
        {
            return $this->hasMany( LegacyDebt::class , 'user_id' , 'id' );
        }

        public function ledgers() : HasMany
        {
            return $this->hasMany( CustomerLedger::class , 'user_id' , 'id' );
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

        protected function totalRevenue() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->orders()->sum( 'total' )
            );
        }

        protected function averageOrderValue() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->orders()->avg( 'total' ) ?? 0
            );
        }

        public function creditAndDeposit() : HasMany
        {
            return $this->orders()->active()->whereIn( 'payment_type' , [ PaymentType::CREDIT , PaymentType::DEPOSIT ] );
        }

        public function creditOrdersQuery() : HasMany
        {
            return $this->creditAndDeposit()
                        ->whereRaw( 'total > (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)' )
                        ->orderBy( 'order_datetime' );
        }

        protected function creditOrders() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->creditOrdersQuery()->get()
            );
        }

        protected function totalCreditOrders() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->creditOrdersQuery()->sum( 'total' )
            );
        }

        protected function credits() : Attribute
        {
            return Attribute::make(
                get: function () {
                    $orderDebt = (float) $this->creditOrdersQuery()
                                              ->reorder()
                                              ->selectRaw( 'SUM(GREATEST(0, orders.total - (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id))) as total_debt' )
                                              ->value( 'total_debt' );

                    $legacyDebt = (float) $this->legacyDebts()->sum( 'amount' );

                    return $orderDebt + $legacyDebt;
                }
            );
        }

        protected function wallet() : Attribute
        {
            return Attribute::make(
                get: function () {
                    return $this->walletTransactions()->sum( 'amount' );
                }
            );
        }

        public function walletTransactions() : HasMany
        {
            return $this->hasMany( CustomerWalletTransaction::class , 'user_id' , 'id' )->latest();
        }

        protected function oldestCreditOrder() : Attribute
        {
            return Attribute::make(
                get: function () {
                    $oldestDate = $this->creditOrdersQuery()->min( 'order_datetime' );

                    return $oldestDate
                        ? round( Carbon::parse( $oldestDate )->diffInHours( now() ) / 24 )
                        : 0;
                }
            );
        }

//        public function shouldSync() : bool
//        {
//            return ! $this->hasRole( \App\Enums\Role::CUSTOMER );
//        }
//        protected static function booted() : void
//        {
//            static::deleted( function ($tenantUser) {
//                // Find the central user using the global_id
//                $centralUser = CentralUser::where( 'global_id' , $tenantUser->global_id )->first();
//
//                if ( $centralUser ) {
//                    $centralUser->delete();
//                }
//            } );
//        }
    }
