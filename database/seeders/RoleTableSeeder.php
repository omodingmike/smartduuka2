<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Spatie\Permission\Models\Role;

    class RoleTableSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            Role::insert( [
                [
                    'name'       => 'Admin' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => 'Customer' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => 'Manager' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => 'POS Operator' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => 'Stuff' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
                [
                    'name'       => 'Distributor' ,
                    'guard_name' => 'web' ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                ] ,
            ] );
        }
    }
