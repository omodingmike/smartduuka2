<?php

    namespace App\Actions\Fortify;

    use App\Enums\Role;
    use App\Models\CentralUser;
    use App\Models\User;
    use Stancl\Tenancy\Events\SyncedResourceSaved;

    class SyncTenantUsersToCentral
    {
        public function sync() : void
        {
            User::withoutRole( Role::CUSTOMER )
                ->get()
                ->each( function (User $user) {
                    $centralUser = CentralUser::where( 'email' , $user->email )->first();
                    if ( $centralUser && $user->global_id !== $centralUser->global_id ) {
                        $user->withoutEvents( function () use ($user , $centralUser) {
                            $user->update( [ 'global_id' => $centralUser->global_id ] );
                        } );
                        $user->refresh();
                    }
                    event( new SyncedResourceSaved( $user , tenancy()->tenant ) );
                } );
        }
    }