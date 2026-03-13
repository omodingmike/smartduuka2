<?php

    use Illuminate\Support\Facades\Broadcast;

    Broadcast::channel('business.{identifier}', function ($user, $identifier) {
        // 1. Get the current tenant (Requires your broadcast auth route to use Stancl middleware)
        $tenant = tenant();

        // 2. Authorize ONLY if the tenant exists and its business_id matches the requested channel
        return $tenant && (string) $tenant->business_id === (string) $identifier;
    });