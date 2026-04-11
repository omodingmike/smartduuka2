<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\EmployeeRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Jobs\SendMailJob;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\User;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Permission\Models\Role;


    class EmployeeService
    {
        public $user;
        public $phoneFilter = [ 'phone' ];
        public $roleFilter  = [ 'role_id' ];
        public $userFilter  = [ 'name' , 'email' , 'username' , 'status' , 'phone' ];
        public $blockRoles  = [ EnumRole::ADMIN , EnumRole::CUSTOMER ];


        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $perPage     = $request->integer( 'perPage' , 10000 );
                $page        = $request->integer( 'page' , 1 );
                $orderColumn = $request->input( 'order_column' ) ?? 'id';
                $orderType   = $request->input( 'order_type' ) ?? 'desc';

                $query = User::with( [ 'media' , 'addresses' ] )
                             ->withoutRole( $this->blockRoles );

                return $query->orderBy( $orderColumn , $orderType )->paginate( perPage: $perPage , page: $page );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function store(EmployeeRequest $request , PinService $pin_service)
        {
            try {
                $role = Role::findById( (int) $request->role_id );

                DB::transaction( function () use ($request , $role , $pin_service) {
                    $pin        = $pin_service->generateUniquePin();
                    $this->user = User::create( [
                        'name'              => $request->name ,
                        'global_id'         => Str::uuid() ,
                        'email'             => $request->email ,
                        'phone'             => $request->phone ,
                        'username'          => $this->username( $request->email ) ,
                        'password'          => bcrypt( $request->password ) ,
                        'status'            => $request->status ,
                        'email_verified_at' => now() ,
                        'country_code'      => $request->country_code ,
                        'is_guest'          => Ask::NO ,
                        'department'        => $request->department ,
                        'raw_pin'           => $pin ,
                        'pin'               => $pin_service->hashPin( $pin ) ,
                        'force_reset'       => $request->boolean( 'forceReset' ) ,
                    ] );

                    $this->user->save();

                    $emailCredentials = $request->boolean( 'emailCredentials' );

                    $this->user->assignRole( $role );
                    if ( $emailCredentials ) {
                        SendMailJob::dispatch( [
                            'name'         => $this->user->name ,
                            'email'        => $this->user->email ,
                            'password'     => $request->password ,
                            'pin'          => $pin ,
                            'login_url'    => 'https://' . tenant( 'id' ) . config( 'session.domain' ) . '/login' ,
                            'company_name' => Settings::group( 'company' )->get( 'company_name' ) ,
                        ] );
                    }
                } );
                return $this->user;

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(EmployeeRequest $request , User $employee , PinService $pin_service)
        {
            try {
                $role = Role::findById( (int) $request->role_id );

                DB::transaction( function () use ($employee , $request , $role , $pin_service) {
                    $this->user               = $employee;
                    $this->user->name         = $request->name;
                    $this->user->email        = $request->email;
                    $this->user->phone        = $request->phone;
                    $this->user->status       = $request->status;
                    $this->user->country_code = $request->country_code;
                    $this->user->department   = $request->department;
                    $this->user->force_reset  = $request->boolean( 'forceReset' );

                    if ( $request->password ) {
                        $this->user->password = Hash::make( $request->password );
                        $emailCredentials     = $request->boolean( 'emailCredentials' );
                        $pin                  = $pin_service->generateUniquePin();
                        $this->user->pin      = $pin_service->hashPin( $pin );
                        if ( $emailCredentials ) {
                            SendMailJob::dispatch( [
                                'name'         => $this->user->name ,
                                'email'        => $this->user->email ,
                                'password'     => $request->password ,
                                'pin'          => $pin ,
                                'login_url'    => 'https://' . tenant( 'id' ) . config( 'session.domain' ) . '/login' ,
                                'company_name' => Settings::group( 'company' )->get( 'company_name' ) ,
                            ] );
                        }
                    }

                    $this->user->save();

                    $this->user->syncRoles( $role );
                } );


                if ( $request->has( 'permissions' ) ) {
                    $permissions = json_decode( $request->permissions , TRUE );
                    if ( $permissions ) {
                        $flatPermissions = [];
                        foreach ( $permissions as $group => $perms ) {
                            if ( is_array( $perms ) ) {
                                $flatPermissions = array_merge( $flatPermissions , $perms );
                            }
                            else {
                                $flatPermissions[] = $perms;
                            }
                        }
                        $this->user->syncPermissions( $flatPermissions );
                    }
                }
                $emailCredentials = $request->boolean( 'emailCredentials' );
                if ( $emailCredentials ) {
                    SendMailJob::dispatch( [
                        'name'         => $this->user->name ,
                        'email'        => $this->user->email ,
                        'password'     => $request->password ,
                        'pin'          => $pin_service->generateUniquePin() ,
                        'login_url'    => 'https://' . tenant( 'id' ) . config( 'session.domain' ) . '/login' ,
                        'company_name' => Settings::group( 'company' )->get( 'company_name' ) ,
                    ] );
                }

                return $this->user;

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(User $employee) : User
        {
            try {
                return $employee;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */

        public function destroy(User $employee)
        {
            try {
                DB::transaction( function () use ($employee) {
                    $employee->addresses()->delete();
                    $employee->delete();
                } );

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
        public function changePassword(UserChangePasswordRequest $request , User $employee) : User
        {
            try {
                $employee->password = Hash::make( $request->password );
                $employee->save();
                return $employee;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changeImage(ChangeImageRequest $request , User $employee) : User
        {
            try {

                if ( $request->image ) {
                    $employee->clearMediaCollection( 'profile' );
                    $employee->addMediaFromRequest( 'image' )->toMediaCollection( 'profile' );
                }
                return $employee;

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }