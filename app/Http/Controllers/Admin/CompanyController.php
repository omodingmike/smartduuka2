<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\PrintMode;
    use App\Http\Requests\CompanyRequest;
    use App\Http\Resources\CompanyResource;
    use App\Services\CompanyService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Smartisan\Settings\Facades\Settings;

    class CompanyController extends AdminController
    {
        public CompanyService $companyService;

        public function __construct(CompanyService $companyService)
        {
            parent::__construct();
            $this->companyService = $companyService;
//        $this->middleware(['permission:settings'])->only('update');
        }

        public function index() : Response | CompanyResource | Application | ResponseFactory
        {
            try {
                return new CompanyResource( $this->companyService->list() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function printing()
        {
            try {
                return response()->json( [ 'data' => Settings::group( 'printing' )->all() ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function getPrintMode()
        {
            try {
                $p = Settings::group( 'printing' )->get( 'print_mode' );
                return response()->json( [ 'data' => [ 'printMode' => (int) $p ?? PrintMode::BOTH->value ] ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function printMode(Request $request)
        {
            try {
                Settings::group( 'printing' )->set( [ 'print_mode' => $request->printMode ] );
                return response()->json( [ 'data' => Settings::group( 'printing' )->all() ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(CompanyRequest $request) : Response | CompanyResource | Application | ResponseFactory
        {
            try {
                return new CompanyResource( $this->companyService->update( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
