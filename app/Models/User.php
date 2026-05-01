<?php

    namespace App\Models;

    use App\Enums\CustomerPaymentType;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentType;
    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Casts\Attribute;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
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
        use InteractsWithMedia , HasApiTokens , HasFactory , HasRoles , Notifiable , ResourceSyncing;

        protected $table = 'users';

        protected $fillable = [
            'name' , 'email' , 'password' , 'username' , 'phone' , 'country_code' , 'is_guest' , 'status' ,
            'email_verified_at' , 'commission' , 'branch_id' , 'phone2' , 'type' , 'notes' , 'pin' ,
            'last_login_date' , 'department' , 'device_token' , 'web_token' , 'balance' , 'remember_token' ,
            'creator_type' , 'creator_id' , 'editor_type' , 'editor_id' , 'commission_paid' ,
            'two_factor_secret' , 'two_factor_recovery_codes' , 'two_factor_confirmed_at' ,
            'force_reset' , 'raw_pin' , 'tenant_id' , 'is_reset' , 'global_id' ,
        ];

        protected $hidden = [ 'password' , 'remember_token' , 'pin' ];

        protected $casts = [
            'id'                => 'integer' ,
            'status'            => Status::class ,
            'email_verified_at' => 'datetime' ,
            'last_login_date'   => 'datetime' ,
            'force_reset'       => 'boolean' ,
            'is_reset'          => 'boolean' ,
        ];

        // =========================================================================
        // STANCL TENANCY & SYNCING METHODS (PRESERVED)
        // =========================================================================

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
        // RELATIONSHIPS (RESTORED & FIXED)
        // =========================================================================

        public function tenant() : BelongsTo
        {
            return $this->belongsTo( Tenant::class );
        }

        public function registers() : HasMany
        {
            return $this->hasMany( Register::class , 'user_id' , 'id' );
        }

        public function creditAndDeposit() : HasMany
        {
//            return $this->orders()->active()->whereIn( 'payment_type' , [ PaymentType::CREDIT , PaymentType::DEPOSIT ] );
            return $this->orders()->whereIn( 'payment_type' , [ PaymentType::CREDIT->value , PaymentType::DEPOSIT->value ] );
        }

        public function unPaidOrdersQuery()
        {
            return $this->creditAndDeposit()
                        ->whereRaw(
                            'total > (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)'
                        );
        }

        public function unPaidOrders() : HasMany
        {
            $creditTypes = self::creditPaymentTypes();

            return $this->orders()
                        ->whereIn( 'payment_type' , $creditTypes )
                        ->whereRaw(
                            'total > (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)'
                        );
        }

        public function scopeWithCredits($query)
        {
            $creditTypes = [ PaymentType::CREDIT->value , PaymentType::DEPOSIT->value ];

            // Logic for Order Debt calculation
            $orderDebtRaw = 'SUM(GREATEST(0, orders.total - (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)))';

            return $query->addSelect( [
                'order_debt_total' => Order::selectRaw( "COALESCE($orderDebtRaw, 0)" )
                                           ->whereColumn( 'user_id' , 'users.id' )
                                           ->whereIn( 'payment_type' , $creditTypes ) ,

                'legacy_debt_total' => LegacyDebt::selectRaw( 'COALESCE(SUM(amount), 0)' )
                                                 ->whereColumn( 'user_id' , 'users.id' ) ,


                'credits' => function ($subquery) use ($orderDebtRaw , $creditTypes) {
                    $subquery->selectRaw( "
                (SELECT COALESCE($orderDebtRaw, 0) FROM orders WHERE orders.user_id = users.id AND orders.payment_type IN (" . implode( ',' , $creditTypes ) . '))
                + 
                (SELECT COALESCE(SUM(amount), 0) FROM legacy_debts WHERE legacy_debts.user_id = users.id)
            ' );
                }
            ] );
        }

        public function scopeWithTotalSpent($query)
        {
            return $query->addSelect( [
                'total_spent' => function ($subquery) {
                    $subquery->selectRaw( 'COALESCE(SUM(pp.amount), 0)' )
                             ->from( 'pos_payments as pp' )
                             ->join( 'orders as o' , 'pp.order_id' , '=' , 'o.id' )
                             ->whereColumn( 'o.user_id' , 'users.id' );
                }
            ] );
        }

        public function orders() : HasMany
        {
            return $this->hasMany( Order::class , 'user_id' , 'id' );
        }

        public function activeOrders() : HasMany
        {
            return $this->orders()->where( 'status' , '<>' , OrderStatus::COMPLETED );
        }

        public function debtPayments() : HasMany
        {
            return $this->hasMany( CustomerPayment::class , 'user_id' , 'id' )
                        ->where( 'customer_payment_type' , CustomerPaymentType::DEBT );
        }

        public function payments() : HasMany
        {
            return $this->hasMany( CustomerPayment::class , 'user_id' , 'id' );
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
            return $this->hasMany( Address::class , 'user_id' , 'id' );
        }

        public function walletTransactions() : HasMany
        {
            return $this->hasMany( CustomerWalletTransaction::class , 'user_id' , 'id' );
        }

        // =========================================================================
        // DYNAMIC SCOPES
        // =========================================================================

        private static function orderDebtSql() : string
        {
            return 'orders.total - (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)';
        }

        private static function creditPaymentTypes() : array
        {
            return [ PaymentType::CREDIT->value , PaymentType::DEPOSIT->value ];
        }

        public function scopeWithDebtMetrics($query)
        {
            $debtSql     = self::orderDebtSql();
            $creditTypes = self::creditPaymentTypes();

            $orderDebtSub = Order::selectRaw( "COALESCE(SUM($debtSql), 0)" )
                                 ->whereColumn( 'user_id' , 'users.id' )
                                 ->whereIn( 'payment_type' , $creditTypes );

            $legacyDebtSub = LegacyDebt::selectRaw( 'COALESCE(SUM(amount), 0)' )
                                       ->whereColumn( 'user_id' , 'users.id' );

            return $query->addSelect( [
                'total_order_debt' => Order::selectRaw( "SUM($debtSql)" )
                                           ->whereColumn( 'user_id' , 'users.id' )
                                           ->whereIn( 'payment_type' , $creditTypes ) ,

                'total_legacy_debt' => LegacyDebt::selectRaw( 'COALESCE(SUM(amount), 0)' )
                                                 ->whereColumn( 'user_id' , 'users.id' ) ,
            ] )->selectRaw(
                '( ' . $orderDebtSub->toSql() . ' ) + ( ' . $legacyDebtSub->toSql() . ' ) as total_credits' ,
                array_merge( $orderDebtSub->getBindings() , $legacyDebtSub->getBindings() )
            );
        }

        protected function register() : Attribute
        {
            return Attribute::make(
                get: fn() => $this->openRegister() ,
            );
        }

        public function openRegister() : Register
        {
            return $this->registers()->whereNull( 'closed_at' )->latest()->first();
        }

        public function scopeWhereHasDebt($query)
        {
            $debtSql     = self::orderDebtSql();
            $creditTypes = self::creditPaymentTypes();

            $orderDebtSub = Order::selectRaw( "COALESCE(SUM($debtSql), 0)" )
                                 ->whereColumn( 'user_id' , 'users.id' )
                                 ->whereIn( 'payment_type' , $creditTypes );

            $legacyDebtSub = LegacyDebt::selectRaw( 'COALESCE(SUM(amount), 0)' )
                                       ->whereColumn( 'user_id' , 'users.id' );

            return $query->whereRaw(
                '( ( ' . $orderDebtSub->toSql() . ' ) + ( ' . $legacyDebtSub->toSql() . ' ) ) > 0' ,
                array_merge( $orderDebtSub->getBindings() , $legacyDebtSub->getBindings() )
            );
        }

        // =========================================================================
        // MEDIA & ATTRIBUTES
        // =========================================================================

        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'profile' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'profile' ) );
            }
            return asset( 'images/required/profile.png' );
        }

        public function getMyRoleAttribute() : ?int
        {
            return $this->roles->first()?->id;
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
    }