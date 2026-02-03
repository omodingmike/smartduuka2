<?php

    namespace App\Services;


    use App\Enums\CacheEnum;
    use App\Enums\SettingsKeyEnum;
    use App\Http\Requests\CleaningSettingRequest;
    use App\Http\Requests\SiteRequest;
    use App\Jobs\UpdateConfigJob;
    use App\Models\Currency;
    use Dipokhalder\EnvEditor\EnvEditor;
    use Exception;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class SiteService
    {
        public EnvEditor $envService;

        public function __construct(EnvEditor $envEditor)
        {
            $this->envService = $envEditor;
        }

        /**
         * @throws Exception
         */
        public function list()
        {
            try {
                return Settings::group( 'site' )->all();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function CleaningSettingList()
        {
            try {
                return Settings::group( SettingsKeyEnum::CLEANING )->all();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(SiteRequest $request)
        {
            $currency                               = Currency::find( $request->site_default_currency );
            $data                                   = $request->validated();
            $data[ 'site_default_currency_symbol' ] = $currency->symbol;
            Settings::group( 'site' )->set( $data );

            $this->envService->addData( [
                'TIMEZONE'               => $request->site_default_timezone ,
                'CURRENCY'               => $currency?->code ,
                'CURRENCY_SYMBOL'        => $currency?->symbol ,
                'CURRENCY_POSITION'      => $request->site_currency_position ,
                'CURRENCY_DECIMAL_POINT' => $request->site_digit_after_decimal_point ,
                'DATE_FORMAT'            => $request->site_date_format ,
                'TIME_FORMAT'            => $request->site_time_format ,
                'NON_PURCHASE_QUANTITY'  => $request->site_non_purchase_product_maximum_quantity
            ] );

            Cache::forget( CacheEnum::CURRENCY_SYMBOL );
            UpdateConfigJob::dispatchAfterResponse();
            return $this->list();
        }

        public function updateCleaning(CleaningSettingRequest $request)
        {
            try {
                $data = $request->validated();
                Settings::group( SettingsKeyEnum::CLEANING )->set( $data );
                return $this->CleaningSettingList();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
