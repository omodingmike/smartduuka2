<?php

    declare( strict_types = 1 );

    namespace App\Providers;

    use App\Events\TenantCreatedEvent;
    use Illuminate\Contracts\Http\Kernel;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Event;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\ServiceProvider;
    use Spatie\Permission\PermissionRegistrar;
    use Stancl\JobPipeline\JobPipeline;
    use Stancl\Tenancy\Events;
    use Stancl\Tenancy\Features\TenantConfig;
    use Stancl\Tenancy\Jobs;
    use Stancl\Tenancy\Listeners;
    use Stancl\Tenancy\Middleware;
    use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

    class TenancyServiceProvider extends ServiceProvider
    {
        // By default, no namespace is used to support the callable array syntax.
        public static string $controllerNamespace = '';

        public function events() : array
        {
            return [
                // Tenant events
                Events\CreatingTenant::class      => [] ,
                Events\TenantCreated::class       => [
                    JobPipeline::make( [
                        Jobs\CreateDatabase::class ,
                        Jobs\MigrateDatabase::class ,
                        Jobs\SeedDatabase::class ,

                        // Your own jobs to prepare the tenant.
                        // Provision API keys, create S3 buckets, anything you want!

                    ] )->send( function (Events\TenantCreated $event) {
                        return $event->tenant;
                    } )->shouldBeQueued() , // `false` by default, but you probably want to make this `true` for production.
                ] ,
                Events\SavingTenant::class        => [] ,
                Events\TenantSaved::class         => [] ,
                Events\UpdatingTenant::class      => [] ,
                Events\TenantUpdated::class       => [] ,
                Events\DeletingTenant::class      => [] ,
                Events\TenantDeleted::class       => [
                    JobPipeline::make( [
                        Jobs\DeleteDatabase::class ,
                    ] )->send( function (Events\TenantDeleted $event) {
                        return $event->tenant;
                    } )->shouldBeQueued( FALSE ) , // `false` by default, but you probably want to make this `true` for production.
                ] ,

                // Domain events
                Events\CreatingDomain::class      => [] ,
                Events\DomainCreated::class       => [] ,
                Events\SavingDomain::class        => [] ,
                Events\DomainSaved::class         => [] ,
                Events\UpdatingDomain::class      => [] ,
                Events\DomainUpdated::class       => [] ,
                Events\DeletingDomain::class      => [] ,
                Events\DomainDeleted::class       => [] ,

                // Database events
                Events\DatabaseCreated::class     => [] ,
                Events\DatabaseMigrated::class    => [] ,
                Events\DatabaseSeeded::class      => [
                    function (Events\DatabaseSeeded $event) {
                        $tenant = $event->tenant;
                        $tenant->update( [ 'ready' => TRUE ] );
                        broadcast( new TenantCreatedEvent( $tenant , Auth::user() ) );
                    }
                ] ,
                Events\DatabaseRolledBack::class  => [] ,
                Events\DatabaseDeleted::class     => [] ,

                // Tenancy events
                Events\InitializingTenancy::class => [] ,
                Events\TenancyInitialized::class  => [
                    Listeners\BootstrapTenancy::class ,
                ] ,

                Events\EndingTenancy::class => [] ,
                Events\TenancyEnded::class  => [
                    Listeners\RevertToCentralContext::class ,
                    function (Events\TenancyEnded $event) {
                        $permissionRegistrar           = app( PermissionRegistrar::class );
                        $permissionRegistrar->cacheKey = 'spatie.permission.cache';
                    }
                ] ,

                Events\BootstrappingTenancy::class                   => [] ,
                Events\TenancyBootstrapped::class                    => [
                    function (Events\TenancyBootstrapped $event) {
                        $permissionRegistrar           = app( PermissionRegistrar::class );
                        $permissionRegistrar->cacheKey = 'spatie.permission.cache.tenant.' . $event->tenancy->tenant->getTenantKey();
                    }
                ] ,
                Events\RevertingToCentralContext::class              => [] ,
                Events\RevertedToCentralContext::class               => [] ,

                // Resource syncing
                Events\SyncedResourceSaved::class                    => [
                    Listeners\UpdateSyncedResource::class ,
                ] ,

                // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
                Events\SyncedResourceChangedInForeignDatabase::class => [] ,
            ];
        }

        public function register()
        {
            //
        }

        public function boot() : void
        {
            $this->bootEvents();
            $this->mapRoutes();

            $this->makeTenancyMiddlewareHighestPriority();
            InitializeTenancyByDomain::$onFail = function () {
                return redirect( config( 'app.url' ) );
            };
            TenantConfig::$storageToConfigMap  = [
                // From app.php
                'APP_NAME'                   => 'app.name' ,
                'PROJECT_ID'                 => 'app.project_id' ,
                'BUSINESS_ID'                => 'app.business_id' ,
                'TIMEZONE'                   => 'app.timezone' ,

                // From at.php
                'AT_USERNAME'                => 'at.username' ,
                'AT_API_KEY'                 => 'at.api_key' ,

                // From system.php
                'DATE_FORMAT'                => 'system.date_format' ,
                'TIME_FORMAT'                => 'system.time_format' ,
                'CURRENCY'                   => 'system.currency' ,
                'CURRENCY_POSITION'          => 'system.currency_position' ,
                'CURRENCY_SYMBOL'            => 'system.currency_symbol' ,
                'CURRENCY_DECIMAL_POINT'     => 'system.currency_decimal_point' ,

                // Standard Mail Overrides (Laravel default config)
                'MAIL_HOST'                  => 'mail.mailers.smtp.host' ,
                'MAIL_PORT'                  => 'mail.mailers.smtp.port' ,
                'MAIL_USERNAME'              => 'mail.mailers.smtp.username' ,
                'MAIL_PASSWORD'              => 'mail.mailers.smtp.password' ,
                'MAIL_ENCRYPTION'            => 'mail.mailers.smtp.encryption' ,
                'MAIL_FROM_ADDRESS'          => 'mail.from.address' ,
                'TELEGRAM_EXCEPTION_TOKEN'   => 'telegram.token' ,
                'TELEGRAM_EXCEPTION_CHAT_ID' => 'telegram.chat_id' ,
            ];
        }

        protected function bootEvents() : void
        {
            foreach ( $this->events() as $event => $listeners ) {
                foreach ( $listeners as $listener ) {
                    if ( $listener instanceof JobPipeline ) {
                        $listener = $listener->toListener();
                    }

                    Event::listen( $event , $listener );
                }
            }
        }

        protected function mapRoutes() : void
        {
            $this->app->booted( function () {
                if ( file_exists( base_path( 'routes/tenant.php' ) ) ) {
                    Route::namespace( static::$controllerNamespace )
                         ->group( base_path( 'routes/tenant.php' ) );
                }
            } );
        }

        protected function makeTenancyMiddlewareHighestPriority() : void
        {
            $tenancyMiddleware = [
                // Even higher priority than the initialization middleware
                Middleware\PreventAccessFromCentralDomains::class ,

                InitializeTenancyByDomain::class ,
                Middleware\InitializeTenancyBySubdomain::class ,
                Middleware\InitializeTenancyByDomainOrSubdomain::class ,
                Middleware\InitializeTenancyByPath::class ,
                Middleware\InitializeTenancyByRequestData::class ,
            ];

            foreach ( array_reverse( $tenancyMiddleware ) as $middleware ) {
                $this->app[ Kernel::class ]->prependToMiddlewarePriority( $middleware );
            }
        }
    }
