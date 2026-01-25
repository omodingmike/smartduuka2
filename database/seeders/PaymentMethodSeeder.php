<?php

    namespace Database\Seeders;

    use App\Models\PaymentMethod;
    use Illuminate\Database\Seeder;

    class PaymentMethodSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run() : void
        {
            PaymentMethod::firstOrCreate( [ 'name' => 'Cash' ] , [
                'name' => 'Cash' , 'merchant_code' => '0000' , 'balance' => 0
            ] );
        }
    }
