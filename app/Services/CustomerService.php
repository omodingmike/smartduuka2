<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\OrderType;
    use App\Enums\PaymentStatus;
    use App\Enums\Role as EnumRole;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\CustomerPaymentRequest;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\CreditDepositPurchase;
    use App\Models\CustomerPayment;
    use App\Models\User;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;


    class CustomerService
    {
        public object $user;
        public array  $phoneFilter = [ 'phone' ];
        public array  $roleFilter  = [ 'role_id' ];
        public array  $userFilter  = [ 'name' , 'email' , 'username' , 'status' , 'phone' ];
        public array  $blockRoles  = [ EnumRole::ADMIN ];


        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return User::with( [ 'media' , 'addresses' ] )->role( EnumRole::CUSTOMER )->where( function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->userFilter ) ) {
                            $query->where( $key , 'like' , '%' . $request . '%' );
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );
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
                    $this->user = User::create( [
                        'username'          => $request->phone ,
                        'name'              => $request->name ,
                        'type'              => $request->type ,
                        'phone'             => $this->username( $request->phone ) ,
                        'password'          => bcrypt( 'password' ) ,
                        'email_verified_at' => now() ,
                        'status'            => $request->status ,
                        'is_guest'          => Ask::NO ,
                    ] );
                    if ( $request->phone2 ) {
                        $this->user->phone2 = $request->phone2;
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
                        $this->user         = $customer;
                        $this->user->name   = $request->name;
                        $this->user->type   = $request->type;
                        $this->user->phone  = $request->phone;
                        $this->user->status = $request->status;
                        if ( $request->email ) {
                            $this->user->email = $request->email;
                        }
                        if ( $request->notes ) {
                            $this->user->notes = $request->notes;
                        }
                        if ( $request->phone2 ) {
                            $this->user->phone2 = $request->phone2;
                        }
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
                    $payment_method = $request->validated()[ 'paymentMethod' ];
                    $date           = parseDate( $request->validated()[ 'date' ] );
                    CustomerPayment::create( [ 'date' => $date , 'amount' => $amount , 'payment_method_id' => $payment_method , 'user_id' => $customer->id ] );
                    $customer->orders()->where( 'balance' , '>' , 0 )
                             ->where( function ($query) use ($date , $payment_method) {
                                 $query->whereOrderType( OrderType::CREDIT );
                                 $query->orWhere( 'order_type' , '=' , OrderType::DEPOSIT );
                             } )
                             ->chunkById( 100 , function ($orders) use (&$amount , $payment_method , $date) {
                                 foreach ( $orders as $order ) {
                                     if ( $amount <= 0 ) {
                                         return FALSE;
                                     }
                                     $creditDepositPurchase = CreditDepositPurchase::where( 'order_id' , $order->id )->latest()->first();
                                     $previousBalance       = $creditDepositPurchase->balance;
//                                     $balance                               = $order->balance;
                                     $balance                               = $previousBalance;
                                     $saveCreditPurchase                    = new CreditDepositPurchase();
                                     $saveCreditPurchase->order_id          = $order->id;
                                     $saveCreditPurchase->user_id           = $order->user_id;
                                     $saveCreditPurchase->payment_method_id = $payment_method;
                                     $saveCreditPurchase->date              = $date;
                                     $saveCreditPurchase->type              = 'credit';
                                     if ( $amount >= $balance ) {
                                         $new_balance = 0;
                                         $order->update(
                                             [
                                                 'balance'        => $new_balance ,
                                                 'payment_status' => PaymentStatus::PAID ,
                                                 'order_type'     => OrderType::POS ,
                                                 'paid'           => $balance ,
                                             ] );
                                         $saveCreditPurchase->paid    = $balance;
                                         $saveCreditPurchase->balance = $new_balance;
                                         $saveCreditPurchase->save();
                                         $amount -= $balance;
                                     }
                                     else {
                                         $order->update(
                                             [
                                                 'balance'        => $balance - $amount ,
                                                 'payment_status' => PaymentStatus::PARTIALLY_PAID ,
                                                 'paid'           => $order->paid + $amount ,
                                             ] );

                                         $saveCreditPurchase->paid    = $amount;
                                         $newCreditBalance            = $previousBalance - $amount;
                                         $saveCreditPurchase->balance = max( $newCreditBalance , 0 );
                                         $saveCreditPurchase->save();
                                         $amount = 0;
                                         return FALSE;
                                     }
                                 }
                             } );
                } );
                return $customer;

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
