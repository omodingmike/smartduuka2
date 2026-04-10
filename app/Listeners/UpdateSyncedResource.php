<?php

    namespace App\Listeners;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Stancl\Tenancy\Contracts\SyncMaster;
    use Stancl\Tenancy\Listeners\UpdateSyncedResource as BaseUpdateSyncedResource;

    use Stancl\Tenancy\Events\SyncedResourceChangedInForeignDatabase;

    class UpdateSyncedResource extends BaseUpdateSyncedResource
    {
        protected function updateResourceInCentralDatabaseAndGetTenants($event, $syncedAttributes)
        {
            /** @var Model|SyncMaster $centralModel */
            $centralModel = $event->model->getCentralModelName()
                                         ::where(
                                             $event->model->getGlobalIdentifierKeyName(),
                                             $event->model->getGlobalIdentifierKey()
                                         )
                                         ->first();

            $event->model->getCentralModelName()::withoutEvents(function () use (&$centralModel, $syncedAttributes, $event) {
                if ($centralModel) {
                    $centralModel->update($syncedAttributes);
                    event(new SyncedResourceChangedInForeignDatabase($event->model, null));
                } else {
                    // Strip 'id' to let the central DB assign its own primary key
                    // and avoid duplicate key violations
                    $attributes = collect($event->model->getAttributes())
                        ->except(['id'])
                        ->toArray();

                    $centralModel = $event->model->getCentralModelName()::create($attributes);
                    event(new SyncedResourceChangedInForeignDatabase($event->model, null));
                }
            });

            $currentTenantMapping = function ($model) use ($event) {
                return ((string) $model->pivot->tenant_id) === ((string) $event->tenant->getTenantKey());
            };

            $mappingExists = $centralModel->tenants->contains($currentTenantMapping);

            if (! $mappingExists) {
                Pivot::withoutEvents(function () use ($centralModel, $event) {
                    $centralModel->tenants()->attach($event->tenant->getTenantKey());
                });
            }

            return $centralModel->tenants->filter(function ($model) use ($currentTenantMapping) {
                return ! $currentTenantMapping($model);
            });
        }
    }