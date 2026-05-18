<?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use App\Models\TenantBranch;
    use App\Models\TenantSubscription;
    use Illuminate\Console\Attributes\Description;
    use Illuminate\Console\Attributes\Signature;
    use Illuminate\Console\Command;

    #[Signature( 'seed-branches' )]
    #[Description( 'Command description' )]
    class SeedBranches extends Command
    {
        public function handle() : void
        {
            $tenants = Tenant::all();
            foreach ( $tenants as $tenant ) {
                $has_main_branch = TenantBranch::where( 'tenant_id' , $tenant->id )->exists();
                if ( ! $has_main_branch ) {
                    $branch = TenantBranch::firstOrCreate( [ 'name' => 'Main Branch' , ] , [
                        'tenant_id'  => $tenant->id ,
                        'can_delete' => FALSE
                    ] );
                    $branch->update( [ 'code' => recordId( 'BR' , $branch , 3 ) ] );
                    TenantSubscription::where( 'tenant_id' , $tenant->id )->update( [ 'branch_id' => $branch->id ] );
                }
            }
        }
    }
