<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreModuleRequest;
    use App\Http\Resources\SiteModuleResource;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class ModuleController extends Controller
    {
        public function index()
        {
            return new SiteModuleResource( $this->list() );
        }

        public function list()
        {
            try {
                return Settings::group( 'module' )->all();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function update(StoreModuleRequest $request)
        {
            try {
                $validatedData = $request->validated();
                $modulesArray  = json_decode( $validatedData[ 'modules' ] , TRUE );
                Settings::group( 'module' )->set( $modulesArray );
                return new SiteModuleResource( $this->list() );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
