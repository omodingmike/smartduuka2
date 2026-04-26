<?php

    namespace App\Listeners;

    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Stancl\Tenancy\Events\SyncedResourceChangedInForeignDatabase;
    use Stancl\Tenancy\Listeners\UpdateSyncedResource as BaseUpdateSyncedResource;

    class UpdateSyncedResource extends BaseUpdateSyncedResource
    {
        protected function updateResourceInCentralDatabaseAndGetTenants($event , $syncedAttributes)
        {
            $centralModel = $event->model->getCentralModelName()
                                         ::where(
                                             $event->model->getGlobalIdentifierKeyName() ,
                                             $event->model->getGlobalIdentifierKey()
                                         )
                                         ->first();

            $event->model->getCentralModelName()::withoutEvents( function () use (&$centralModel , $syncedAttributes , $event) {
                $attributes = collect( $event->model->getAttributes() )->except( [ 'id' ] )->toArray();

                $lookup = [];
                if ( isset( $attributes[ 'username' ] ) ) {
                    $lookup[ 'username' ] = $attributes[ 'username' ];
                }
                else {
                    $lookup[ $event->model->getGlobalIdentifierKeyName() ] = $event->model->getGlobalIdentifierKey();
                }

                $centralModel = $event->model->getCentralModelName()::updateOrCreate(
                    $lookup ,
                    $attributes
                );

                event( new SyncedResourceChangedInForeignDatabase( $event->model , NULL ) );
            } );

            $centralModel->load( 'tenants' );

            $currentTenantMapping = fn($model) => (string) $model->pivot->tenant_id === (string) $event->tenant->getTenantKey();

            $mappingExists = $centralModel->tenants->contains( $currentTenantMapping );

            if ( ! $mappingExists ) {
                Pivot::withoutEvents( function () use ($centralModel , $event) {
                    $centralModel->tenants()->attach( $event->tenant->getTenantKey() );
                } );
            }

            return $centralModel->tenants->filter( fn($model) => ! $currentTenantMapping( $model ) );
        }
    }
