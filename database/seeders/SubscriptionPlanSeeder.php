<?php

    namespace Database\Seeders;

    use App\Enums\SubscriptionPlanType;
    use App\Models\SubscriptionPlan;
    use Illuminate\Database\Seeder;

    class SubscriptionPlanSeeder extends Seeder
    {

        public function run() : void
        {
            $plans = [
                /**
                 * Existing Customer Packages
                 */
                [
                    'name'        => 'Starter' ,
                    'description' => 'Ideal for new businesses.' ,
                    'base_amount' => 26000 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Existing ,
                    'features'    => [ 'Up to 3 registers' , 'Basic inventory' ] ,
                ] ,
                [
                    'name'        => 'Professional' ,
                    'description' => 'For established businesses.' ,
                    'base_amount' => 52000 ,
                    'popular'     => TRUE ,
                    'type'        => SubscriptionPlanType::Existing ,
                    'features'    => [ 'Up to 10 registers' , 'Advanced analytics' ] ,
                ] ,
                /**
                 * New Customer Packages
                 */
                [
                    'name'        => 'Starter' ,
                    'description' => 'For small shops & kiosks' ,
                    'base_amount' => 26000 ,
                    'setup'       => 150000 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Starter ,
                    'features'    => [
                        '1-3 Registers (POS)' => TRUE ,
                        'Basic Inventory'     => TRUE ,
                        'Daily Reports'       => TRUE ,
                        'Email Support'       => TRUE ,
                        'Multi-warehouse'     => FALSE ,
                        'HR & Payroll'        => FALSE ,
                    ] ,
                ] ,
                [
                    'name'        => 'Professional' ,
                    'description' => 'For growing businesses' ,
                    'base_amount' => 52000 ,
                    'setup'       => 300000 ,
                    'popular'     => TRUE ,
                    'type'        => SubscriptionPlanType::Starter ,
                    'features'    => [
                        'Unlimited Registers'   => TRUE ,
                        'Advanced Warehousing'  => TRUE ,
                        'Manufacturing'         => TRUE ,
                        'HR & Payroll'          => TRUE ,
                        'Accounting'            => TRUE ,
                        'Priority 24/7 Support' => TRUE ,
                    ] ,
                ]
                ,
            ];
            foreach ( $plans as $plan ) {
                SubscriptionPlan::updateOrCreate( [ 'name' => $plan[ 'name' ] , 'type' => $plan[ 'type' ] ] , $plan );
            }
        }
    }
