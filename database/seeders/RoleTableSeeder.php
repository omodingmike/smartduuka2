<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Role;

    class RoleTableSeeder extends Seeder
    {
        public function run() : void
        {
            $roles = [
                [ 'name' => EnumRole::ADMIN , 'guard_name' => 'sanctum' ] ,
                [ 'name' => EnumRole::CUSTOMER , 'guard_name' => 'sanctum' ] ,
                [ 'name' => EnumRole::MANAGER , 'guard_name' => 'sanctum' ] ,
                [ 'name' => EnumRole::POS_OPERATOR , 'guard_name' => 'sanctum' ] ,
                [ 'name' => EnumRole::STUFF , 'guard_name' => 'sanctum' ] ,
                [ 'name' => EnumRole::DISTRIBUTOR , 'guard_name' => 'sanctum' ] ,
            ];

            $definedRoles = array_column( $roles , 'name' );

            $existingRoles = Role::where( 'guard_name' , 'sanctum' )->pluck( 'name' )->toArray();

            $missingRoles = array_diff( $definedRoles , $existingRoles );

            if ( empty( $missingRoles ) ) {
                return;
            }

            foreach ( $roles as $roleData ) {
                if ( in_array( $roleData[ 'name' ] , $missingRoles ) ) {
                    Role::firstOrCreate(
                        [ 'name' => $roleData[ 'name' ] , 'guard_name' => $roleData[ 'guard_name' ] ] ,
                        $roleData
                    );
                }
            }
        }
    }