<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Role;

    class RoleTableSeeder extends Seeder
    {
        public function run() : void
        {
            Role::insert( [
                [
                    'name'       => EnumRole::ADMIN ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => EnumRole::CUSTOMER ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => EnumRole::MANAGER ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => EnumRole::POS_OPERATOR ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => EnumRole::STUFF ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => EnumRole::DISTRIBUTOR ,
                    'guard_name' => 'sanctum' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
            ] );
        }
    }