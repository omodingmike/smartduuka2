<?php

    namespace Database\Seeders;


    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        public function run() : void
        {
            $this->call( PrinterTemplateSeeder::class );
            $this->call( WarehouseSeeder::class );
            $this->call( LocationSeed::class );

            $this->call( RoleTableSeeder::class );
            $this->call( PermissionTableSeeder::class );
            $this->call( UserTableSeeder::class );
        }
    }
