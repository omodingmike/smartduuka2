<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;

    class DeleteTenant extends Command
    {
        protected $signature = 'delete-tenant {id}';

        protected $description = 'Delete a tenant and its database.';

        public function handle() : void
        {
            $id = $this->argument( 'id' );

            $this->info( "Finding tenant {$id}..." );

            $tenant = Tenant::find( $id );

            if ( $tenant ) {
                $this->info( "Deleting tenant {$id}..." );
                $tenant->delete();
                $this->info( "Tenant {$id} deleted successfully." );
            }
            else {
                $this->error( "Tenant {$id} not found." );
            }
        }
    }
