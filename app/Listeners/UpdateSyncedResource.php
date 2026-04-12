<?php

    namespace App\Listeners;

    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Stancl\Tenancy\Events\SyncedResourceChangedInForeignDatabase;
    use Stancl\Tenancy\Listeners\UpdateSyncedResource as BaseUpdateSyncedResource;

    class UpdateSyncedResource extends BaseUpdateSyncedResource
    {
        protected function updateResourceInCentralDatabaseAndGetTenants($event, $syncedAttributes)
        {
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
                    $centralModel = $event->model->getCentralModelName()::create(
                        collect($event->model->getAttributes())
                            ->except(['id'])
                            ->toArray()
                    );
                    event(new SyncedResourceChangedInForeignDatabase($event->model, null));
                }
            });

            // Force fresh load — avoids stale/missing relationship cache
            $centralModel->load('tenants');

            $currentTenantMapping = fn($model) =>
                (string) $model->pivot->tenant_id === (string) $event->tenant->getTenantKey();

            $mappingExists = $centralModel->tenants->contains($currentTenantMapping);

            if (!$mappingExists) {
                Pivot::withoutEvents(function () use ($centralModel, $event) {
                    $centralModel->tenants()->attach($event->tenant->getTenantKey());
                });
            }

            return $centralModel->tenants->filter(fn($model) => !$currentTenantMapping($model));
        }
    }