<?php

    namespace App\Actions\Fortify;

    use App\Enums\Role;
    use App\Models\User;
    use Stancl\Tenancy\Events\SyncedResourceSaved;

    class SyncTenantUsersToCentral
    {
        public function sync() : void
        {
            User::withoutRole( Role::CUSTOMER )
                ->get()
                ->each( function (User $user) {
                    event( new SyncedResourceSaved( $user , tenancy()->tenant ) );
                } );
        }
    }