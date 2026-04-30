<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\CustomerPaymentType;
    use App\Enums\PaymentStatus;
    use App\Enums\PaymentType;
    use App\Enums\PosPaymentType;
    use App\Enums\Role as EnumRole;
    use App\Enums\Status;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\CustomerPaymentRequest;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Http\Resources\SimpleCustomerResource;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\CustomerLedger;
    use App\Models\CustomerPayment;
    use App\Models\User;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;

    class CustomerService
    {
        public object $user;
        public array  $blockRoles = [ EnumRole::ADMIN ];

        private function authorizeNotBlocked() : void
        {
            if ( in_array( EnumRole::CUSTOMER , $this->blockRoles ) ) {
                throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
            }
        }

        // =========================================================================
        // SHARED DB SUBQUERIES
        // These return sub-select Builder instances for use with addSelect().
        // Keeping them here avoids duplicating SQL across list() and simpleList().
        // =========================================================================

        /**
         * Correlated subquery: total amount paid across all pos_payments for a customer.
         */
        private function totalSpentSubquery() : \Illuminate\Database\Query\Builder
        {
            return DB::table( 'pos_payments' )
                     ->join( 'orders' , 'orders.id' , '=' , 'pos_payments.order_id' )
                     ->whereColumn( 'orders.user_id' , 'users.id' )
                     ->selectRaw( 'COALESCE(SUM(pos_payments.amount), 0)' );
        }

        /**
         * Correlated subquery: sum of outstanding credit-order debt (order total minus payments).
         * Mirrors the PHP logic in User::credits but runs entirely in the DB.
         */
        private function creditOrderDebtSubquery() : \Illuminate\Database\Query\Builder
        {
            return DB::table( 'orders' )
                     ->whereColumn( 'orders.user_id' , 'users.id' )
                     ->whereIn( 'orders.payment_type' , [
                         PaymentType::CREDIT->value ,
                         PaymentType::DEPOSIT->value ,
                     ] )
                     ->whereRaw(
                         'orders.total > (SELECT COALESCE(SUM(pp.amount),0) FROM pos_payments pp WHERE pp.order_id = orders.id)'
                     )
                     ->selectRaw(
                         'COALESCE(SUM(
                             GREATEST(0, orders.total - (
                                 SELECT COALESCE(SUM(pp.amount), 0)
                                 FROM pos_payments pp
                                 WHERE pp.order_id = orders.id
                             ))
                         ), 0)'
                     );
        }

        /**
         * Correlated subquery: sum of unpaid legacy debts for a customer.
         */
        private function legacyDebtSubquery() : \Illuminate\Database\Query\Builder
        {
            return DB::table( 'legacy_debts' )
                     ->whereColumn( 'legacy_debts.user_id' , 'users.id' )
                     ->selectRaw( 'COALESCE(SUM(amount), 0)' );
        }

        /**
         * Correlated subquery: total debt payments already made by a customer.
         */
        private function debtPaidSubquery() : \Illuminate\Database\Query\Builder
        {
            return DB::table( 'customer_payments' )
                     ->whereColumn( 'customer_payments.user_id' , 'users.id' )
                     ->where( 'customer_payment_type' , CustomerPaymentType::DEBT->value )
                     ->selectRaw( 'COALESCE(SUM(amount), 0)' );
        }

        /**
         * Correlated subquery: total credit order face value (before payments).
         */
        private function totalCreditOrdersSubquery() : \Illuminate\Database\Query\Builder
        {
            return DB::table( 'orders' )
                     ->whereColumn( 'orders.user_id' , 'users.id' )
                     ->whereIn( 'orders.payment_type' , [
                         PaymentType::CREDIT->value ,
                         PaymentType::DEPOSIT->value ,
                     ] )
                     ->whereRaw(
                         'orders.total > (SELECT COALESCE(SUM(pp.amount),0) FROM pos_payments pp WHERE pp.order_id = orders.id)'
                     )
                     ->selectRaw( 'COALESCE(SUM(orders.total), 0)' );
        }

        // =========================================================================
        // PUBLIC SERVICE METHODS
        // =========================================================================

        /**
         * Full customer list query used by index() and export().
         *
         * OPTIMIZATIONS vs previous version:
         *   1. credits is now a DB subquery (order debt + legacy debt) — no PHP loop.
         *   2. debt_paid is a DB subquery — CustomerResource no longer calls
         *      $this->debtPayments->sum('amount') in PHP.
         *   3. total_credit_orders is a DB subquery — User::$totalCreditOrders
         *      attribute no longer re-queries per customer.
         *   4. Removed the redundant bare `orders.posPayments` eager load — it was
         *      never used in CustomerResource; only creditAndDeposit.posPayments is.
         *   5. Removed bare `walletTransactions` collection load — wallet balance is
         *      already covered by withSum(...as wallet). The resource only renders
         *      walletTransactions when loaded, which show() handles separately.
         *   6. Removed `payments` (all payments) — the resource only needs
         *      `debtPayments` on the list view. `payments` is only needed in show().
         *   7. creditAndDeposit.posPayments no longer loaded twice (was in both the
         *      root with() list and inside the creditAndDeposit closure).
         *
         * @throws Exception
         */
        public function list(Request $request) : Builder
        {
            $debtors = $request->boolean( 'debtors' );
            $query   = $request->input( 'query' );

            return User::select( 'users.*' )
                       ->addSelect( [
                           'total_spent'         => $this->totalSpentSubquery() ,
                           'debt_paid'           => $this->debtPaidSubquery() ,
                           'total_credit_orders' => $this->totalCreditOrdersSubquery() ,
                           'wallet'              => DB::table( 'customer_wallet_transactions' )
                                                      ->whereColumn( 'user_id' , 'users.id' )
                                                      ->selectRaw( 'COALESCE(SUM(amount), 0)' ) ,
                       ] )
                       ->selectRaw(
                           '(' . $this->creditOrderDebtSubquery()->toSql() . ') + (' . $this->legacyDebtSubquery()->toSql() . ') as credits' ,
                           array_merge(
                               $this->creditOrderDebtSubquery()->getBindings() ,
                               $this->legacyDebtSubquery()->getBindings()
                           )
                       )
                       ->withCount( [
                           'orders as order_count' => fn($q) => $q->active() ,
                       ] )
                       ->with( [
                           // Media for avatars
                           'media' ,
                           // Debt payments with their method (shown in resource list)
                           'debtPayments.paymentMethod' ,
                           // Ledger entries
                           'ledgers' ,
                           // Addresses
                           'addresses' ,
                           // Credit orders with aggregates and line items
                           'creditAndDeposit' => fn($q) => $q
                               ->withSum( 'posPayments as total_paid' , 'amount' )
                               ->withCount( [ 'orderProducts as items_count' ] )
                               ->with( [
                                   'orderProducts' => fn($q) => $q->select( 'id' , 'order_id' , 'item_id' , 'quantity' ) ,
                                   'orderProducts.item:id,name' ,
                               ] ) ,
                       ] )
                       ->role( EnumRole::CUSTOMER )
                       ->when( $query , fn($q) => $q->where( 'name' , 'ilike' , '%' . $query . '%' ) )
                       ->when( $debtors , function ($q) {
                           // Filter to only customers who have outstanding debt —
                           // use EXISTS correlated subqueries instead of whereHas
                           // (avoids a COUNT(*) that PostgreSQL must fully materialise).
                           $q->where( function ($inner) {
                               $inner->whereExists( function ($sub) {
                                   $sub->from( 'orders' )
                                       ->whereColumn( 'orders.user_id' , 'users.id' )
                                       ->whereIn( 'orders.payment_type' , [
                                           PaymentType::CREDIT->value ,
                                           PaymentType::DEPOSIT->value ,
                                       ] )
                                       ->whereRaw(
                                           'orders.total > (SELECT COALESCE(SUM(pp.amount),0) FROM pos_payments pp WHERE pp.order_id = orders.id)'
                                       );
                               } )
                                     ->orWhereExists( function ($sub) {
                                         $sub->from( 'legacy_debts' )
                                             ->whereColumn( 'legacy_debts.user_id' , 'users.id' )
                                             ->where( 'amount' , '>' , 0 );
                                     } );
                           } );
                       } )
                       ->orderByDesc( 'created_at' );
        }

        /**
         * Lightweight list for dropdowns / autocomplete.
         *
         * OPTIMIZATIONS:
         *   Same DB subquery pattern as list(), but strips the heavy
         *   creditAndDeposit eager load that's not needed here.
         */
        public function simpleList(Request $request) : AnonymousResourceCollection
        {
            $query    = $request->input( 'query' );
            $paginate = $request->boolean( 'paginate' , TRUE );
            $per_page = $request->integer( 'per_page' , 10 );
            $page     = $request->integer( 'page' , 1 );
            $debtors  = $request->boolean( 'debtors' );

            $customers = User::select( 'users.*' )
                             ->role( EnumRole::CUSTOMER )
                             ->addSelect( [
                                 'total_spent' => $this->totalSpentSubquery() ,
                                 'debt_paid'   => $this->debtPaidSubquery() ,
                                 'wallet'      => DB::table( 'customer_wallet_transactions' )
                                                    ->whereColumn( 'user_id' , 'users.id' )
                                                    ->selectRaw( 'COALESCE(SUM(amount), 0)' ) ,
                             ] )
                             ->selectRaw(
                                 '(' . $this->creditOrderDebtSubquery()->toSql() . ') + (' . $this->legacyDebtSubquery()->toSql() . ') as credits' ,
                                 array_merge(
                                     $this->creditOrderDebtSubquery()->getBindings() ,
                                     $this->legacyDebtSubquery()->getBindings()
                                 )
                             )
                             ->withCount( [
                                 'orders as order_count' => fn($q) => $q->active() ,
                             ] )
                             ->with( [ 'debtPayments.paymentMethod' , 'ledgers' ] )
                             ->when( $debtors , function ($q) {
                                 $q->where( function ($inner) {
                                     $inner->whereExists( function ($sub) {
                                         $sub->from( 'orders' )
                                             ->whereColumn( 'orders.user_id' , 'users.id' )
                                             ->whereIn( 'orders.payment_type' , [
                                                 PaymentType::CREDIT->value ,
                                                 PaymentType::DEPOSIT->value ,
                                             ] )
                                             ->whereRaw(
                                                 'orders.total > (SELECT COALESCE(SUM(pp.amount),0) FROM pos_payments pp WHERE pp.order_id = orders.id)'
                                             );
                                     } )
                                           ->orWhereExists( function ($sub) {
                                               $sub->from( 'legacy_debts' )
                                                   ->whereColumn( 'legacy_debts.user_id' , 'users.id' )
                                                   ->where( 'amount' , '>' , 0 );
                                           } );
                                 } );
                             } )
                             ->when( $query , fn($q) => $q->where( 'name' , 'ilike' , '%' . $query . '%' ) )
                             ->orderByDesc( 'created_at' );

            $result = $paginate
                ? $customers->paginate( perPage: $per_page , page: $page )
                : $customers->get();

            return SimpleCustomerResource::collection( $result );
        }

        /**
         * @throws Exception
         */
        public function store(CustomerRequest $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $this->user = User::create( array_filter( [
                        'username'          => $request->phone ?? $request->name ,
                        'commission'        => 0 ,
                        'name'              => $request->name ,
                        'type'              => $request->type ,
                        'password'          => bcrypt( 'password' ) ,
                        'email_verified_at' => now() ,
                        'status'            => $request->status ?? Status::ACTIVE ,
                        'is_guest'          => Ask::NO ,
                        'phone'             => $request->phone ,
                        'phone2'            => $request->phone2 ,
                        'notes'             => $request->notes ,
                        'email'             => $request->email ,
                    ] , fn($v) => $v !== NULL ) );

                    $this->user->assignRole( EnumRole::CUSTOMER );
                } );

                return $this->user;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(CustomerRequest $request , User $customer)
        {
            try {
                $this->authorizeNotBlocked();

                DB::transaction( function () use ($customer , $request) {
                    $data = array_filter( [
                        'name'   => $request->name ,
                        'type'   => $request->type ,
                        'phone'  => $request->phone ,
                        'status' => $request->status ,
                        'email'  => $request->email ,
                        'notes'  => $request->notes ,
                        'phone2' => $request->phone2 ,
                    ] , fn($v) => $v !== NULL );

                    if ( $request->password ) {
                        $data[ 'password' ] = Hash::make( $request->password );
                    }

                    $customer->fill( $data )->save();
                    $this->user = $customer;
                } );

                return $this->user;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws \Throwable
         */
        public function payment(CustomerPaymentRequest $request , User $customer)
        {
            try {
                return DB::transaction( function () use ($customer , $request) {
                    $amount         = $request->validated()[ 'amount' ];
                    $payment_method = $request->validated()[ 'method' ];
                    $reference      = 'DP-' . time();

                    $customer->load( [
                        'legacyDebts' => fn($q) => $q
                            ->whereNotIn( 'payment_status' , [ PaymentStatus::PAID ] )
                            ->orderByDesc( 'created_at' ) ,
                    ] );

                    $creditOrders = $customer->creditOrdersQuery()->get();

                    $payment = CustomerPayment::create( [
                        'date'                  => now() ,
                        'amount'                => $amount ,
                        'payment_method_id'     => $payment_method ,
                        'customer_payment_type' => CustomerPaymentType::DEBT ,
                        'user_id'               => $customer->id ,
                        'balance'               => $customer->credits - $amount ,
                    ] );

                    $runningBalance = (float) $customer->credits;

                    foreach ( $customer->legacyDebts as $debt ) {
                        if ( $amount <= 0 ) break;

                        $debtAmount = (float) $debt->amount;

                        if ( $amount >= $debtAmount ) {
                            $debt->update( [ 'amount' => 0 , 'payment_status' => PaymentStatus::PAID ] );
                            addToLedger(
                                user: $customer ,
                                reference: $reference ,
                                bill_amount: $debtAmount ,
                                paid: $debtAmount
                            );
                            $amount         -= $debtAmount;
                            $runningBalance -= $debtAmount;
                        }
                        else {
                            $debt->decrement( 'amount' , $amount );
                            $debt->update( [ 'payment_status' => PaymentStatus::PARTIALLY_PAID ] );
                            $runningBalance -= $amount;

                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => $reference ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $debtAmount ,
                                'paid'        => $amount ,
                                'balance'     => $runningBalance ,
                            ] );

                            $amount = 0;
                        }
                    }

                    foreach ( $creditOrders as $order ) {
                        if ( $amount <= 0 ) break;

                        $balance = (float) $order->balance;

                        if ( $amount >= $balance ) {
                            $order->update( [ 'payment_status' => PaymentStatus::PAID ] );
                            addPayment( $order , $balance , $payment_method , $reference , PosPaymentType::DEBT );
                            $runningBalance -= $balance;

                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => $reference ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $balance ,
                                'paid'        => $balance ,
                                'balance'     => $runningBalance ,
                            ] );

                            $amount -= $balance;
                        }
                        else {
                            $order->update( [
                                'payment_status' => PaymentStatus::PARTIALLY_PAID ,
                                'paid'           => $amount ,
                            ] );
                            addPayment( $order , $amount , $payment_method , $reference , PosPaymentType::DEBT );
                            $runningBalance -= $amount;

                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => $reference ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $balance ,
                                'paid'        => $amount ,
                                'balance'     => $runningBalance ,
                            ] );

                            $amount = 0;
                        }
                    }

                    $payment->setRelation( 'customer' , $customer );
                    $payment->reference = $reference;
                    $payment->creator   = auth()->user();

                    return $payment;
                } );
            } catch ( Exception | \Throwable $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(User $customer) : User
        {
            try {
                $this->authorizeNotBlocked();

                $customer->load( [
                    'media' ,
                    'walletTransactions' ,
                    'debtPayments.paymentMethod' ,
                    'payments.paymentMethod' ,
                    'ledgers' ,
                    'addresses' ,
                    'legacyDebts' ,
                    'creditAndDeposit' => fn($q) => $q
                        ->withSum( 'posPayments as total_paid' , 'amount' )
                        ->withCount( [ 'orderProducts as items_count' ] )
                        ->with( [
                            'orderProducts' => fn($q) => $q->select( 'id' , 'order_id' , 'item_id' , 'quantity' ) ,
                            'orderProducts.item:id,name' ,
                        ] ) ,
                ] );

                return $customer;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(User $customer)
        {
            try {
                $this->authorizeNotBlocked();

                if ( ! $customer->hasRole( EnumRole::CUSTOMER ) ) {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }

                if ( $customer->id === 2 ) {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }

                DB::transaction( function () use ($customer) {
                    $customer->addresses()->delete();
                    $customer->delete();
                } );
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changePassword(UserChangePasswordRequest $request , User $customer) : User
        {
            try {
                $this->authorizeNotBlocked();
                $customer->password = Hash::make( $request->password );
                $customer->save();
                return $customer;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changeImage(ChangeImageRequest $request , User $customer) : User
        {
            try {
                $this->authorizeNotBlocked();

                if ( $request->image ) {
                    $customer->clearMediaCollection( 'profile' );
                    $customer->addMediaFromRequest( 'image' )->toMediaCollection( 'profile' );
                }

                return $customer;
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        private function username(string $email) : string
        {
            $emails = explode( '@' , $email );
            return $emails[ 0 ] . mt_rand();
        }
    }