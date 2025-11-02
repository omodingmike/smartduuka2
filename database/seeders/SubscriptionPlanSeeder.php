<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{

    public function run(): void
    {
        $data = [
            [
                'name'     => 'Monthly',
                'amount'   => 50000,
                'duration' => 1,
            ],
            [
                'name'     => 'Quarterly',
                'amount'   => 145000,
                'duration' => 3,
            ],
            [
                'name'     => 'Half Yearly',
                'amount'   => 285000,
                'duration' => 6,
            ],
            [
                'name'     => 'Yearly',
                'amount'   => 555000,
                'duration' => 12,
            ],
        ];
        foreach ($data as $datum) {
            SubscriptionPlan::create($datum);
        }
    }
}
