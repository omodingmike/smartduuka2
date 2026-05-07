<?php

    namespace Database\Seeders;

    use App\Models\SubscriptionPlan;
    use Illuminate\Database\Seeder;

    class SubscriptionPlanSeeder extends Seeder
    {

        public function run() : void
        {
            $plans = [
                [
                    'name'        => 'Starter' ,
                    'description' => 'Ideal for new businesses.' ,
                    'base_amount' => 26000 ,
                    'popular'     => FALSE ,
                    'features'    => [ 'Up to 3 registers' , 'Basic inventory' ] ,
                ]
                ,
                [
                    'name'        => 'Professional' ,
                    'description' => 'For established businesses.' ,
                    'base_amount' => 52000 ,
                    'popular'     => TRUE ,
                    'features'    => [ 'Up to 10 registers' , 'Advanced analytics' ] ,
                ]
                ,
            ];
            foreach ( $plans as $plan ) {
                SubscriptionPlan::updateOrCreate( [ 'name' => $plan[ 'name' ] ] , $plan );
            }
        }
    }
