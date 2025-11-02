<?php

namespace Database\Seeders;


use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Smartisan\Settings\Facades\Settings;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Settings::group('company')->set([
            'company_name'         => 'PosKing - Laravel POS System with Inventory Management and Retail Business ERP',
            'company_email'        => 'info@inilabs.net',
            'company_calling_code' => '+880',
            'company_phone'        => '13333846282',
            'company_website'      => 'https://foodking.dev',
            'company_city'         => 'Mirpur 1',
            'company_state'        => 'Dhaka',
            'company_country_code' => 'BGD',
            'company_zip_code'     => '1216',
            'company_latitude'     => '23.7699072',
            'company_longitude'    => '90.3643136',
            'company_address'      => 'House : 25, Road No: 2, Block A, Mirpur-1, Dhaka 1216'
        ]);

        $envService = new EnvEditor();
        $envService->addData([
            'APP_NAME' => "PosKing - Laravel POS System with Inventory Management and Retail Business ERP"
        ]);
        Artisan::call('optimize:clear');
    }
}