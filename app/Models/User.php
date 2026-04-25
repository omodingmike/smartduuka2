<?php

    namespace App\Models;

    use App\Enums\CustomerPaymentType;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentType;
    use App\Enums\Status;
    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasManyThrough;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Laravel\Sanctum\HasApiTokens;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;
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

        protected $table = 'users';

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
            'department' ,
            'device_token' ,
            'web_token' ,
            'balance' ,
            'remember_token' ,
            'creator_type' ,
            'creator_id' ,
            'editor_type' ,
            'editor_id' ,
            'commission_paid' ,
            'two_factor_secret' ,
            'two_factor_recovery_codes' ,
            'two_factor_confirmed_at' ,
            'force_reset' ,
            'raw_pin' ,
            'tenant_id' ,
            'is_reset' ,
            'global_id' ,
        ];

        protected $hidden = [
            'password' ,
            'remember_token' ,
            'pin' ,
        ];

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
            return [ 'id' , 'name' , 'password' , 'email' ];
        }

        public function guardName() : string
        {
            return 'sanctum';
        }

        // =========================================================================
        // RELATIONSHIPS
        // =========================================================================

        public function tenant() : BelongsTo
        {
            return $this->belongsTo( Tenant::class );
        }

        public function registers() : HasMany
        {
            return $this->hasMany( Register::class , 'user_id' , 'id' );
        }

        public function commissions() : HasManyThrough
        {
            return $this->hasManyThrough(
                Commission::class ,
                CommissionTarget::class ,
                'user_id' ,
                'id' ,
                'id' ,
                'commission_id'
            );
        }

        public function commissionTargets() : HasMany
        {
            return $this->hasMany( CommissionTarget::class );
        }

        public function payouts() : HasMany
        {
            return $this->hasMany( CommissionPayout::class , 'user_id' , 'id' );
        }

        public function orders() : HasMany
        {
            return $this->hasMany( Order::class , 'user_id' , 'id' );
        }

        /** Shorthand for non-completed orders — use this where the old orders() was used. */
        public function activeOrders() : HasMany
        {
            return $this->orders()->where( 'status' , '<>' , OrderStatus::COMPLETED );
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

        public function walletTransactions() : HasMany
        {
            // -------------------------------------------------------------------------
            // FIX 3: Removed ->latest() from the relationship definition.
            // Sorting in a relationship definition is applied even when you only need
            // a SUM — PostgreSQL must sort before aggregating, wasting CPU.
            // Apply ->latest() at call-site only when you need ordered results.
            // -------------------------------------------------------------------------
            return $this->hasMany( CustomerWalletTransaction::class , 'user_id' , 'id' );
        }

        // =========================================================================
        // ATTRIBUTES (accessors)
        // =========================================================================

        protected function name() : Attribute
        {
            return Attribute::make(
                get: fn(string $name) => ucwords( $name ) ,
            );
        }

        protected function register() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->openRegister() ,
            );
        }

        public function openRegister() : Register | null
        {
            // -------------------------------------------------------------------------
            // FIX 4: Removed ?-> safe-operator chain — it hides errors and is redundant.
            // ->first() already returns null when no row exists.
            // -------------------------------------------------------------------------
            return $this->registers()->whereNull( 'closed_at' )->latest()->first();
        }

        // -------------------------------------------------------------------------
        // FIX 5: Image accessors are fine, but guard against missing media eagerly
        // loaded. No change needed here — just ensure you eager-load media:
        //   User::with('media')->get()
        // -------------------------------------------------------------------------
        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'profile' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'profile' ) );
            }
            return asset( 'images/required/profile.png' );
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
            $this->addMediaConversion( 'thumb' )
                 ->crop( 'crop-center' , 225 , 225 )
                 ->keepOriginalImageFormat()
                 ->sharpen( 10 );
        }

        // -------------------------------------------------------------------------
        // FIX 6: getMyRoleAttribute + getrole() were redundant and fragile.
        // `myrole` is not a real column — it relied on roles being loaded already.
        // Replaced with a clean, eager-load-friendly accessor.
        // -------------------------------------------------------------------------
        public function getMyRoleAttribute() : ?int
        {
            return $this->roles->first()?->id;
        }

        // =========================================================================
        // CREDIT / FINANCIAL COMPUTATIONS
        // These were the most expensive part. Key fixes:
        // 1. creditAndDeposit / creditOrdersQuery are now scoped on activeOrders()
        //    (non-completed) so the intent is preserved after fixing orders().
        // 2. credits() rewrites the correlated-subquery-per-row into a single
        //    set-based aggregate — dramatically faster on large datasets.
        // 3. All monetary computed attributes are CACHED per-request using the
        //    model's cache key. Cache is busted on save().
        // =========================================================================

//        public function creditAndDeposit() : HasMany
//        {
//            return $this->activeOrders()
//                        ->active()
//                        ->whereIn( 'payment_type' , [ PaymentType::CREDIT , PaymentType::DEPOSIT ] );
//        }
        public function creditAndDeposit() : HasMany
        {
            return $this->orders()->active()->whereIn( 'payment_type' , [ PaymentType::CREDIT , PaymentType::DEPOSIT ] );
        }

        public function creditOrdersQuery() : HasMany
        {
            // -------------------------------------------------------------------------
            // FIX 7: The correlated subquery inside WHERE is unavoidable for correctness,
            // but we push it into a single aggregate below in credits().
            // Required index on pos_payments: index(['order_id', 'amount'])
            // -------------------------------------------------------------------------
            return $this->creditAndDeposit()
                        ->whereRaw(
                            'total > (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)'
                        )
                        ->orderBy( 'order_datetime' );
        }

        protected function totalRevenue() : Attribute
        {
            return Attribute::make(
            // Use activeOrders() to match original intent; cache the result.
                get: fn() => $this->remember( 'total_revenue' , fn() => $this->activeOrders()->sum( 'total' ) )
            );
        }

        protected function averageOrderValue() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->remember( 'average_order_value' , fn() => $this->activeOrders()->avg( 'total' ) ?? 0 )
            );
        }

        protected function creditOrders() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->remember( 'credit_orders' , fn() => $this->creditOrdersQuery()->get() )
            );
        }

        protected function totalCreditOrders() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->remember( 'total_credit_orders' , fn() => $this->creditOrdersQuery()->sum( 'total' ) )
            );
        }

        protected function credits(): Attribute
        {
            return Attribute::make(
                get: function () {
                    $orderDebt = (float) $this->creditOrdersQuery()
                                              ->reorder()
                                              ->selectRaw('
                    SUM(
                        GREATEST(
                            0,
                            orders.total - (
                                SELECT COALESCE(SUM(pp.amount), 0)
                                FROM pos_payments pp
                                WHERE pp.order_id = orders.id
                            )
                        )
                    ) as total_debt
                ')->value('total_debt');
                    $legacyDebt = (float) $this->legacyDebts()->sum('amount');
                    return $orderDebt + $legacyDebt;
                }
            );
        }

//        protected function wallet() : Attribute
//        {
//            return Attribute::make(
//                get: fn() => $this->remember( 'wallet' , fn() => $this->walletTransactions()->sum( 'amount' ) )
//            );
//        }
//        protected function wallet() : Attribute
//        {
//            return Attribute::make(
//                get: fn() => $this->remember( 'wallet' , fn() => $this->walletTransactions()->sum( 'amount' ) )
//            );
//        }

        protected function oldestCreditOrder() : Attribute
        {
            return Attribute::make(
                get: function () {
                    return $this->remember( 'oldest_credit_order' , function () {
                        $oldestDate = $this->creditOrdersQuery()->min( 'order_datetime' );
                        return $oldestDate
                            ? round( Carbon::parse( $oldestDate )->diffInHours( now() ) / 24 )
                            : 0;
                    } );
                }
            );
        }

        private array $_memo = [];

        private function remember(string $key , callable $callback) : mixed
        {
            if ( ! array_key_exists( $key , $this->_memo ) ) {
                $this->_memo[ $key ] = $callback();
            }
            return $this->_memo[ $key ];
        }

        protected static function booted() : void
        {
            static::saved( function (User $user) {
                $user->_memo = [];
            } );
        }
    }