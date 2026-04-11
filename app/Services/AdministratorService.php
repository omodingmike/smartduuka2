<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Http\Requests\AdministratorRequest;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Jobs\SendMailJob;
    use App\Libraries\AppLibrary;
    use App\Models\User;
    use Exception;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Smartisan\Settings\Facades\Settings;

    class AdministratorService
    {
        public $user;

        /**a
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $page        = $request->integer( 'page' , 1 );
                $perPage     = $request->integer( 'perPage' );
                $orderColumn = $request->input( 'order_column' ) ?? 'id';
                $orderType   = $request->input( 'order_type' ) ?? 'desc';

                $query = User::with( [ 'media' ] )
                             ->role( EnumRole::ADMIN )
                             ->orderBy( $orderColumn , $orderType );
                if ( $perPage ) {
                    return $query->paginate( perPage: $perPage , page: $page );
                }
                return $query->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function store(AdministratorRequest $request , PinService $pin_service)
        {
            try {
                DB::transaction( function () use ($request , $pin_service) {
                    $forceReset       = $request->boolean( 'forceReset' );
                    $emailCredentials = $request->boolean( 'emailCredentials' );
                    $pin              = $pin_service->generateUniquePin();
                    $this->user       = User::create( [
                        'name'              => $request->name ,
                        'email'             => $request->email ,
                        'global_id'         => Str::uuid() ,
                        'phone'             => $request->phone ,
                        'username'          => AppLibrary::username( $request->name ) ,
                        'password'          => Hash::make( $request->password ) ,
                        'status'            => $request->status ,
                        'force_reset'       => $forceReset ,
                        'raw_pin'           => $pin ,
                        'pin'               => $pin_service->hashPin( $pin ) ,
                        'email_verified_at' => now() ,
                        'country_code'      => $request->country_code ,
                        'is_guest'          => Ask::NO ,
                    ] );
                    if ( $request->pin ) {
                        $this->user->pin = Hash::make( $request->pin );
                    }
                    $this->user->save();
                    $this->user->assignRole( EnumRole::ADMIN );
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
        public function update(AdministratorRequest $request , User $administrator , PinService $pin_service)
        {
            try {
                DB::transaction( function () use ($administrator , $request , $pin_service) {
                    $forceReset               = $request->boolean( 'forceReset' );
                    $emailCredentials         = $request->boolean( 'emailCredentials' );
                    $this->user               = $administrator;
                    $this->user->name         = $request->name;
                    $this->user->email        = $request->email;
                    $this->user->phone        = $request->phone;
                    $this->user->status       = $request->status;
                    $this->user->force_reset  = $forceReset;
                    $this->user->country_code = $request->country_code;

                    if ( $request->password ) {
                        $this->user->password = Hash::make( $request->password );
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
                    if ( $request->pin ) {
                        $this->user->pin = Hash::make( $request->pin );
                    }
                    $this->user->save();
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
        public function destroy(User $administrator)
        {
            try {
                if ( Auth::user()->id != $administrator->id && $administrator->id != 1 ) {
                    if ( $administrator->hasRole( EnumRole::ADMIN ) ) {
                        DB::transaction( function () use ($administrator) {
                            $administrator->removeRole( $administrator->roles[ 0 ]->id );
                            $administrator->addresses()->delete();
                            $administrator->delete();
                        } );
                    }
                    else {
                        throw new Exception( trans( 'The permission is denied.' ) , 422 );
                    }
                }
                else {
                    throw new Exception( trans( 'The permission is denied.' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(User $administrator) : User
        {
            try {
                if ( $administrator->hasRole( EnumRole::ADMIN ) ) {
                    return $administrator;
                }
                else {
                    throw new Exception( trans( 'The permission is denied.' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changePassword(UserChangePasswordRequest $request , User $administrator) : User
        {
            try {
                if ( $administrator->hasRole( EnumRole::ADMIN ) ) {
                    $administrator->password = Hash::make( $request->password );
                    $administrator->save();
                    return $administrator;
                }
                else {
                    throw new Exception( trans( 'The permission is denied.' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function changeImage(ChangeImageRequest $request , User $administrator) : User
        {
            try {
                if ( $administrator->hasRole( EnumRole::ADMIN ) ) {
                    $administrator->clearMediaCollection( 'profile' );
                    $administrator->addMediaFromRequest( 'image' )->toMediaCollection( 'profile' );
                    return $administrator;
                }
                else {
                    throw new Exception( trans( 'The permission is denied.' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }