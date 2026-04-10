<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Schema;
    use Spatie\Permission\PermissionRegistrar;

    class RoleTableSeeder extends Seeder
    {
        public function run() : void
        {
            $tableNames = config( 'permission.table_names' );

            if ( ! Schema::hasTable( $tableNames[ 'roles' ] ) ) {
                Artisan::call( 'tenants:migrate');
                Log::warning( "Skipping RoleTableSeeder: Table {$tableNames['roles']} does not exist." );
//                return;
            }

            // 2. Clear the Spatie cache to ensure we aren't using stale central data
            app()[ PermissionRegistrar::class ]->forgetCachedPermissions();

            $roles = [
                [ 'name' => EnumRole::ADMIN , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::CUSTOMER , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::MANAGER , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::POS_OPERATOR , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::STUFF , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
                [ 'name' => EnumRole::DISTRIBUTOR , 'guard_name' => 'sanctum' , 'created_at' => now() , 'updated_at' => now() ] ,
            ];

            $definedRoleNames = array_column( $roles , 'name' );

            // 3. Use DB::table to check for existing roles (bypasses Eloquent issues)
            $existingRoleNames = DB::table( $tableNames[ 'roles' ] )
                                   ->where( 'guard_name' , 'sanctum' )
                                   ->pluck( 'name' )
                                   ->toArray();

            $missingRoleNames = array_diff( $definedRoleNames , $existingRoleNames );

            if ( empty( $missingRoleNames ) ) {
                return;
            }

            $rolesToInsert = array_filter( $roles , function ($role) use ($missingRoleNames) {
                return in_array( $role[ 'name' ] , $missingRoleNames );
            } );

            // 4. Perform a bulk insert
            DB::table( $tableNames[ 'roles' ] )->insert( array_values( $rolesToInsert ) );
        }
    }