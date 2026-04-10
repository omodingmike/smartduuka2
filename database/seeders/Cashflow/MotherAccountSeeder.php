<?php

    namespace Database\Seeders\Cashflow;

    use App\Models\Cashflow\MotherAccount;
    use Illuminate\Database\Seeder;

    class MotherAccountSeeder extends Seeder
    {

        public function run() : void
        {
            MotherAccount::firstOrCreate( [ 'name' => 'Business Mother Account' ] );
        }
    }
