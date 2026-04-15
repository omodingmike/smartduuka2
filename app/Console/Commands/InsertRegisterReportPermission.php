<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Spatie\Permission\Models\Permission;

    class InsertRegisterReportPermission extends Command
    {
        protected $signature = 'insert-register-report';

        protected $description = 'Inserts the Register Reports permission into the database';

        public function handle() : int
        {
            Tenant::all()->runForEach( function (Tenant $tenant) {
                $parentPermission = Permission::where( 'title' , 'like' , '%Reports%' )->first();

                if ( ! $parentPermission ) {
                    $this->error( "Parent permission 'Reports' not found. Please seed the permissions first." );
                    return 1;
                }

                $permission = Permission::firstOrCreate(
                    [
                        'name'       => 'register_reports',
                        'guard_name' => 'sanctum'
                    ],
                    [
                        'title'  => 'Register Reports',
                        'url'    => 'report-register',
                        'parent' => $parentPermission->parent,
                    ]
                );

                if ( $permission->wasRecentlyCreated ) {
                    $this->info( 'Permission "Register Reports" created successfully.' );
                }
                else {
                    $this->info( 'Permission "Register Reports" already exists.' );
                }
            } );
            return 0;
        }
    }
