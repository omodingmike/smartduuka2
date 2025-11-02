<?php

    namespace App\Http\Controllers;

    use App\Mail\AdminRoyaltyCustomerRegistrationMail;
    use App\Models\RoyaltyCustomer;
    use App\Models\ThemeSetting;
    use Illuminate\Support\Facades\URL;
    use Smartisan\Settings\Facades\Settings;

    class TestController extends Controller
    {
        public function seedDatabase()
        {
            try {
                $settings             = Settings::group('company')->all();
                $logo                 = ThemeSetting::where([ 'key' => 'theme_logo' ])->first()->logo;
                $company_name         = $settings['company_name'];
                $company_email        = $settings['company_email'];
                $company_phone        = $settings['company_phone'];
                $company_website      = $settings['company_website'];
                $company_city         = $settings['company_city'];
                $company_state        = $settings['company_state'];
                $company_country_code = $settings['company_country_code'];
                $company_zip_code     = $settings['company_zip_code'];
                $company_address      = $settings['company_address'];
                $royalty_customer     = RoyaltyCustomer::latest()->first();
                $url                  = URL::to('/') . '/royalty/customers/frontend/show/' . $royalty_customer->id;
                return new AdminRoyaltyCustomerRegistrationMail(customer_email: $royalty_customer->email , app_email: $royalty_customer->email , name:
                    $royalty_customer->name , link: $url , qr_code: $royalty_customer->qr_code,logo: $logo);
            } catch ( \Exception $e ) {
                return response()->json([ 'message' => $e->getMessage() ]);
            }
        }
    }
