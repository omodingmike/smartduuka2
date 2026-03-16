<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\EmployeeRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\User;
    use Exception;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;
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

        public function store(EmployeeRequest $request)
        {
            try {
                $role = Role::findById( (int) $request->role_id );

                DB::transaction( function () use ($request , $role) {
                    $this->user = User::create( [
                        'name'              => $request->name ,
                        'email'             => $request->email ,
                        'phone'             => $request->phone ,
                        'username'          => $this->username( $request->email ) ,
                        'password'          => bcrypt( $request->password ) ,
                        'status'            => $request->status ,
                        'email_verified_at' => now() ,
                        'country_code'      => $request->country_code ,
                        'is_guest'          => Ask::NO ,
                        'department'        => $request->department ,
                        'pin'               => $request->pin ,
                        'force_reset'       => $request->boolean( 'forceReset' ) ,
                    ] );

                    $this->user->assignRole( $role );
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
        public function update(EmployeeRequest $request , User $employee)
        {
            try {
                $role = Role::findById( (int) $request->role_id );

                DB::transaction( function () use ($employee , $request , $role) {
                    $this->user               = $employee;
                    $this->user->name         = $request->name;
                    $this->user->email        = $request->email;
                    $this->user->phone        = $request->phone;
                    $this->user->status       = $request->status;
                    $this->user->country_code = $request->country_code;
                    $this->user->department   = $request->department;
                    $this->user->pin          = $request->pin;
                    $this->user->force_reset  = $request->boolean( 'forceReset' );

                    if ( $request->password ) {
                        $this->user->password = Hash::make( $request->password );
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