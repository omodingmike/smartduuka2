<?php

    namespace Database\Seeders;

    use App\Enums\DefaultPaymentMethods;
    use App\Models\PaymentMethod;
    use Illuminate\Database\Seeder;

    class PaymentMethodSeeder extends Seeder
    {
        public function run() : void
        {
            PaymentMethod::firstOrCreate( [ 'name' => 'Cash' ] , [
                'name' => 'Cash' , 'merchant_code' => '0000' , 'balance' => 0
            ] );
            PaymentMethod::firstOrCreate( [ 'name' => DefaultPaymentMethods::WALLET->value ] , [
                'name' => DefaultPaymentMethods::WALLET->value , 'merchant_code' => '0000' , 'balance' => 0
            ] );
        }
    }
