<?php

    namespace App\Services;

    use App\Models\RoyaltyCustomer;

    class DiscountService
    {
        public function discount(RoyaltyCustomer $customer)
        {
            return $customer->royaltyPackage->benefits()->max('discount');
        }
    }
