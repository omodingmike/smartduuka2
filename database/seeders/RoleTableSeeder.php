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
                [
                    'name'       => EnumRole::ADMIN ,
                    'guard_name' => 'sanctum' ,
                ] ,
                [
                    'name'       => EnumRole::CUSTOMER ,
                    'guard_name' => 'sanctum' ,
                ] ,
                [
                    'name'       => EnumRole::MANAGER ,
                    'guard_name' => 'sanctum' ,
                ] ,
                [
                    'name'       => EnumRole::POS_OPERATOR ,
                    'guard_name' => 'sanctum' ,
                ] ,
                [
                    'name'       => EnumRole::STAFF ,
                    'guard_name' => 'sanctum' ,
                ] ,
                [
                    'name'       => EnumRole::DISTRIBUTOR ,
                    'guard_name' => 'sanctum' ,
                ] ,
            ];

            foreach ( $roles as $roleData ) {
                Role::firstOrCreate(
                    [ 'name' => $roleData[ 'name' ] , 'guard_name' => $roleData[ 'guard_name' ] ] ,
                    $roleData
                );
            }
        }
    }
