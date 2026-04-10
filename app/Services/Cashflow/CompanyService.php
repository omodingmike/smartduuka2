<?php

    namespace App\Services\Cashflow;

    use App\Http\Requests\CompanyRequest;
    use App\Http\Resources\CompanyResource;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class CompanyService
    {
        public function __construct() {}

        public function list() : CompanyResource
        {
            try {
                return new CompanyResource( Settings::group( 'company' )->all() );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function update(CompanyRequest $request) : CompanyResource
        {
            $data = $request->validated();
            Settings::group( 'company' )->set( $data );
            tenant()->update( [
                'APP_NAME' => $request->company_name
            ] );
            return $this->list();
        }
    }
