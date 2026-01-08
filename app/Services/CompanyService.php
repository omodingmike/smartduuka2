<?php

    namespace App\Services;

    use App\Http\Requests\CompanyRequest;
    use App\Jobs\UpdateConfig;
    use App\Models\Business;
    use Dipokhalder\EnvEditor\EnvEditor;
    use Exception;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class CompanyService
    {
        public $envService;

        public function __construct()
        {
            $this->envService = new EnvEditor();
        }

        /**
         * @throws Exception
         */
        public function list()
        {
            try {
                return Settings::group( 'company' )->all();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(CompanyRequest $request)
        {
            try {
                $data = $request->validated();
                Settings::group( 'company' )->set( $data );
                $this->envService->addData( [ 'APP_NAME' => $request->company_name ] );
                Business::where( [ 'project_id' => config( 'app.project_id' ) ] )
                        ->update( [ 'business_name' => Settings::group( 'company' )->get( 'company_name' ) , 'phone_number' => phoneNumber() ] );
                UpdateConfig::dispatchAfterResponse();
                return $this->list();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
