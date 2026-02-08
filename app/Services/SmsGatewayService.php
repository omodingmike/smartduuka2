<?php

    namespace App\Services;

    use App\Http\Requests\SmsGatewayRequest;
    use Dipokhalder\EnvEditor\EnvEditor;
    use Exception;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class SmsGatewayService
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
                return Settings::group( 'sms_gateway' )->all();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(SmsGatewayRequest $request)
        {
            try {
                Settings::group( 'sms_gateway' )->set( $request->validated() );

//                $this->envService->addData( [
//                    'AT_USERNAME' => $request->at_username ,
//                    'AT_API_KEY'  => $request->at_apikey ,
//                ] );
//                Artisan::call( 'config:cache' );
                tenant()->update([
                    'AT_USERNAME' => $request->at_username,
                    'AT_API_KEY'  => $request->at_apikey,
                ]);

                return $this->list();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
