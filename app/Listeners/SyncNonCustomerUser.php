<?php

    namespace App\Listeners;

    use App\Enums\Role;
    use App\Models\User;
    use Stancl\Tenancy\Events\SyncedResourceSaved;

    class SyncNonCustomerUser
    {
        /**
         * @throws \Exception
         */
        public function handle(SyncedResourceSaved $event) : void
        {
            $model = $event->model;
            if ( $model instanceof User ) {
                if ( $model->hasRole( Role::CUSTOMER ) ) {
                    return;
                }
            }

            app( \Stancl\Tenancy\Listeners\UpdateSyncedResource::class )->handle( $event );
        }
    }
