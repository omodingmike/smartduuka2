<?php

    namespace Database\Seeders;

    use App\Models\BillingCycle;
    use Illuminate\Database\Seeder;

    class BillingCycleSeeder extends Seeder
    {

        public function run() : void
        {
            $cycles = [
                [
                    'name'       => 'Monthly' ,
                    'multiplier' => 1 ,
                    'discount'   => 0 ,
                ] ,
                [
                    'name'       => 'Quarterly' ,
                    'multiplier' => 3 ,
                    'discount'   => 0.1
                ] ,
                [
                    'name'       => 'Half Year' ,
                    'multiplier' => 6 ,
                    'discount'   => 0.15
                ] ,
                [
                    'name'       => 'Yearly' ,
                    'multiplier' => 12 ,
                    'discount'   => 0.2
                ]
            ];
            foreach ( $cycles as $cycle ) {
                BillingCycle::updateOrCreate(
                    [ 'name' => $cycle[ 'name' ] ] ,
                    $cycle
                );
            }
        }
    }
