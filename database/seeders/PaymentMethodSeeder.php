<?php

    namespace Database\Seeders;

    use App\Enums\DefaultPaymentMethods;
    use App\Enums\MediaEnum;
    use App\Models\PaymentMethod;
    use Illuminate\Database\Seeder;
    use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
    use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

    class PaymentMethodSeeder extends Seeder
    {
        /**
         * @throws FileDoesNotExist
         * @throws FileIsTooBig
         */
        public function run() : void
        {
            $cash = PaymentMethod::firstOrCreate( [ 'name' => 'Cash' ] , [
                'name' => 'Cash' , 'merchant_code' => '0000' , 'balance' => 0
            ] );
            $cash
                ->addMedia( public_path( 'cash.png' ) )
                ->preservingOriginal()
                ->toMediaCollection( MediaEnum::IMAGES_COLLECTION );

            $wallet = PaymentMethod::firstOrCreate( [ 'name' => DefaultPaymentMethods::WALLET->value ] , [
                'name' => DefaultPaymentMethods::WALLET->value , 'merchant_code' => '0000' , 'balance' => 0
            ] );

            $wallet
                ->addMedia( public_path( 'wallet.png' ) )
                ->preservingOriginal()
                ->toMediaCollection( MediaEnum::IMAGES_COLLECTION );

        }
    }
