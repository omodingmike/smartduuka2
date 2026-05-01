<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\CustomerPaymentType;
    use App\Enums\PaymentStatus;
    use App\Enums\PosPaymentType;
    use App\Enums\Role as EnumRole;
    use App\Enums\Status;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\CustomerPaymentRequest;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\CustomerLedger;
    use App\Models\CustomerPayment;
    use App\Models\User;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Http\Request;
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

        public function list(Request $request) : Builder
        {
            $debtors = $request->boolean( 'debtors' );
            $query   = $request->input( 'query' );

            return User::query()
                       ->withDebtMetrics()
                       ->withTotalSpent()
                       ->withSum( 'walletTransactions as wallet_balance' , 'amount' )
                       ->withCount( [ 'orders as order_count' ] )
                       ->with( [
                           'media' , 'debtPayments.paymentMethod' , 'ledgers' , 'addresses' ,'unPaidOrders.posPayments.paymentMethod'
                       ] )
                       ->role( EnumRole::CUSTOMER )
                       ->when( $query , fn($q) => $q->where( 'name' , 'ilike' , '%' . $query . '%' ) )
                       ->when( $debtors , fn($q) => $q->whereHasDebt() )
                       ->orderByDesc( 'created_at' );
        }

        public function simpleList(Request $request)
        {
            $query    = $request->input( 'query' );
            $paginate = $request->boolean( 'paginate' , TRUE );
            $per_page = $request->integer( 'per_page' , 10 );
            $page     = $request->integer( 'page' , 1 );
            $debtors  = $request->boolean( 'debtors' );
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

                    $creditOrders = $customer->unPaidOrdersQuery()->get();

                    $payment = CustomerPayment::create( [
                        'date'                  => now() ,
                        'amount'                => $amount ,
                        'payment_method_id'     => $payment_method ,
                        'customer_payment_type' => CustomerPaymentType::DEBT ,
                        'user_id'               => $customer->id ,
                        'balance'               => userCredit( $customer) - $amount ,
                    ] );

                    $runningBalance = (float) userCredit( $customer );

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
                $customer->load( [
                    'media' ,
                    'walletTransactions' ,
                    'debtPayments.paymentMethod' ,
                    'payments.paymentMethod' ,
                    'ledgers' ,
                    'addresses' ,
                    'legacyDebts' ,
                    'creditOrDepositOrders' => fn($q) => $q
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