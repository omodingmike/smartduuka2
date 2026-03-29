<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\CustomerPaymentType;
    use App\Enums\PaymentStatus;
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


        /**
         * @throws Exception
         */
        public function list(Request $request) : Builder
        {
            try {
                $query   = $request->input( 'query' ) ?? NULL;
                $debtors = $request->boolean( 'debtors' );

                return User::with( [ 'media' , 'addresses' , 'debtPayments' , 'ledgers' ] )
                           ->role( EnumRole::CUSTOMER )
                           ->when( $query , function ($q) use ($query) {
                               $q->where( 'name' , 'ilike' , "%" . $query . "%" );
                           } )
                           ->when( $debtors , function ($q) {
                               $q->whereHas( 'orders' , function ($query) {
                                   $query->active()->whereRaw( 'total > (SELECT COALESCE(SUM(amount), 0) FROM pos_payments WHERE pos_payments.order_id = orders.id)' );
                               } );
                           } )
                           ->orderBy( 'created_at' , 'desc' );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function store(CustomerRequest $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $status     = $request->status;
                    $this->user = User::create( [
                        'username'          => $request->phone ?? $request->name ,
                        'commission'        => 0 ,
                        'name'              => $request->name ,
                        'type'              => $request->type ,
                        'password'          => bcrypt( 'password' ) ,
                        'email_verified_at' => now() ,
                        'status'            => $status ? $request->status : Status::ACTIVE ,
                        'is_guest'          => Ask::NO ,
                    ] );
                    if ( $request->phone2 ) {
                        $this->user->phone2 = $request->phone2;
                    }
                    if ( $request->phone ) {
                        $this->user->phone = $request->phone;
                    }
                    if ( $request->notes ) {
                        $this->user->notes = $request->notes;
                    }
                    if ( $request->email ) {
                        $this->user->email = $request->email;
                    }
                    $this->user->save();
                    $this->user->assignRole( EnumRole::CUSTOMER );
                } );
                return $this->user;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(CustomerRequest $request , User $customer)
        {
            try {
                if ( ! in_array( EnumRole::CUSTOMER , $this->blockRoles ) ) {
                    DB::transaction( function () use ($customer , $request) {
                        $this->user = $customer;

                        // Core fields matching store logic
                        $this->user->name   = $request->name;
                        $this->user->type   = $request->type;
                        $this->user->phone  = $request->phone;
                        $this->user->status = $request->status;

                        // Conditional fields matching store logic
                        if ( $request->email ) {
                            $this->user->email = $request->email;
                        }

                        if ( $request->notes ) {
                            $this->user->notes = $request->notes;
                        }

                        if ( $request->phone2 ) {
                            $this->user->phone2 = $request->phone2;
                        }

                        // Password handling (specific to update)
                        if ( $request->password ) {
                            $this->user->password = Hash::make( $request->password );
                        }

                        $this->user->save();
                    } );

                    return $this->user;
                }
                else {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws \Throwable
         */
        public function payment(CustomerPaymentRequest $request , User $customer)
        {
            try {
                DB::transaction( function () use ($customer , $request) {
                    $amount         = $request->validated()[ 'amount' ];
                    $payment_method = $request->validated()[ 'method' ];
                    $date           = now();
                    $customer->load( [
                        'legacyDebts' => function ($query) {
                            $query->whereNotIn( 'payment_status' , [ PaymentStatus::PAID ] )
                                  ->latest();
                        } ,
//                        'orders'      => function ($query) {
//                            $query->active()
//                                  ->withSum( 'posPayments' , 'amount' )
//                                  ->orderBy( 'order_datetime' , 'asc' );
//                        }
                    ] );

                    CustomerPayment::create( [
                        'date'                  => $date ,
                        'amount'                => $amount ,
                        'payment_method_id'     => $payment_method ,
                        'customer_payment_type' => CustomerPaymentType::DEBT ,
                        'user_id'               => $customer->id ,
                        'balance'               => $customer->credits - $amount ,
                    ] );

                    foreach ( $customer->legacyDebts as $debt ) {
                        if ( $amount <= 0 ) break;
                        $debt_amount = $debt->amount;
                        if ( $amount >= $debt_amount ) {
//                            $debt->decrement( 'amount' , $debt_amount );
                            $debt->update( [
                                'amount'         => 0 ,
                                'payment_status' => PaymentStatus::PAID
                            ] );
                            addToLedger( user: $customer , reference: 'Debt Payment' , bill_amount: $debt_amount , paid: $debt_amount );

                            $amount -= $debt_amount;
                        }
                        else {
                            $debt->decrement( 'amount' , $amount );
                            $debt->update( [
                                'payment_status' => PaymentStatus::PARTIALLY_PAID
                            ] );
                            $customer->refresh();
                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => time() ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $debt_amount ,
                                'paid'        => $amount ,
                                'balance'     => $customer->credits
                            ] );

                            $amount = 0;
                        }
                    }

                    foreach ( $customer->credit_orders as $order ) {
                        if ( $amount <= 0 ) break;

                        $balance = $order->balance;

                        if ( $amount >= $balance ) {
                            $order->update( [ 'payment_status' => PaymentStatus::PAID ] );
                            addPayment( $order , $balance , $payment_method );
//                            addToLedger( user: $customer , reference: 'Debt Payment' , bill_amount: $balance , paid: $balance );
                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => time() ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $balance ,
                                'paid'        => $balance ,
                                'balance'     => $customer->credits
                            ] );
                            $amount -= $balance;
                        }
                        else {
                            $order->update( [
                                'payment_status' => PaymentStatus::PARTIALLY_PAID ,
                                'paid'           => $amount
                            ] );
                            addPayment( $order , $amount , $payment_method );
                            $customer->refresh();
                            CustomerLedger::create( [
                                'user_id'     => $customer->id ,
                                'date'        => now() ,
                                'reference'   => time() ,
                                'description' => 'Debt Payment' ,
                                'bill_amount' => $balance ,
                                'paid'        => $amount ,
                                'balance'     => $customer->credits
                            ] );
                            $amount = 0;
                        }
                    }
                } );
                return $customer->refresh();

            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            } catch ( \Throwable $e ) {
                DB::rollBack();
                Log::info( $e->getMessage() );
                throw new Exception( $e->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(User $customer) : User
        {
            try {
                if ( ! in_array( EnumRole::CUSTOMER , $this->blockRoles ) ) {
                    $customer->load( 'walletTransactions');
                    return $customer;
                }
                else {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(User $customer)
        {
            try {
                if ( ! in_array( EnumRole::CUSTOMER , $this->blockRoles ) && $customer->id != 2 ) {
                    if ( $customer->hasRole( EnumRole::CUSTOMER ) ) {
                        DB::transaction( function () use ($customer) {
                            $customer->addresses()->delete();
                            $customer->delete();
                        } );
                    }
                    else {
                        throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                    }
                }
                else {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        private function username($email) : string
        {
            $emails = explode( '@' , $email );
            return $emails[ 0 ] . mt_rand();
        }

        /**
         * @throws Exception
         */
        public function changePassword(UserChangePasswordRequest $request , User $customer) : User
        {
            try {
                if ( ! in_array( EnumRole::CUSTOMER , $this->blockRoles ) ) {
                    $customer->password = Hash::make( $request->password );
                    $customer->save();
                    return $customer;
                }
                else {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changeImage(ChangeImageRequest $request , User $customer) : User
        {
            try {
                if ( ! in_array( EnumRole::CUSTOMER , $this->blockRoles ) ) {
                    if ( $request->image ) {
                        $customer->clearMediaCollection( 'profile' );
                        $customer->addMediaFromRequest( 'image' )->toMediaCollection( 'profile' );
                    }
                    return $customer;
                }
                else {
                    throw new Exception( trans( 'all.message.permission_denied' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }