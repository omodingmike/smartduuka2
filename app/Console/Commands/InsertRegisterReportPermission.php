<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;
    use Spatie\Permission\Models\Permission;

    class InsertRegisterReportPermission extends Command
    {
        protected $signature   = 'tenants:insert-register-report {--tenant=}';
        protected $description = 'Fix permissions sequence and insert Register Reports permission';

        public function handle() : int
        {
            $tenants = $this->option( 'tenant' )
                ? Tenant::where( 'id' , $this->option( 'tenant' ) )->get()
                : Tenant::all();

            $tenants->runForEach( function (Tenant $tenant) {

                $this->info( "Processing tenant: {$tenant->id}" );

                try {
                    DB::statement( "
                    SELECT setval(
                        pg_get_serial_sequence('permissions', 'id'),
                        COALESCE((SELECT MAX(id) FROM permissions), 1),
                        true
                    )
                " );

                    $parentPermission = Permission::where( 'title' , 'like' , '%Reports%' )->first();

                    if ( ! $parentPermission ) {
                        $this->error( "Parent permission 'Reports' not found." );
                        return;
                    }

                    $permission = Permission::firstOrCreate(
                        [
                            'name'       => 'register_reports' ,
                            'guard_name' => 'sanctum' ,
                        ] ,
                        [
                            'title'  => 'Register Reports' ,
                            'url'    => 'report-register' ,
                            'parent' => $parentPermission->id ,
                        ]
                    );

                    $this->info(
                        $permission->wasRecentlyCreated
                            ? 'Created: Register Reports'
                            : 'Already exists: Register Reports'
                    );

                    $this->line( '' );

                } catch ( \Throwable $e ) {
                    $this->error( "Tenant {$tenant->id} failed: " . $e->getMessage() );
                }
            } );

            return 0;
        }
    }