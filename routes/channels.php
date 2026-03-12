<?php

    use App\Models\Tenant;
    use Illuminate\Support\Facades\Broadcast;

    Broadcast::channel( 'channel.{id}' , function ($user , $id) {
        return (int) $user->id === (int) $id;
    } );

    Broadcast::channel( 'business.{identifier}' , function ($user , $identifier) {
        $token = request()->bearerToken();

        if ( ! $token ) {
            return FALSE;
        }

        $tenant = Tenant::where( 'custom_domain' , $identifier )
                        ->orWhere( 'business_id' , $identifier )
                        ->first();

        if ( ! $tenant || empty( $tenant->print_agent_token ) ) {
            return FALSE;
        }

        return hash_equals( $tenant->print_agent_token , $token );
    } );