<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\TaxRequest;
    use App\Http\Resources\TaxResource;
    use App\Models\Tax;
    use App\Services\TaxService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;

    class TaxController extends AdminController
    {
        public TaxService $taxService;

        public function __construct(TaxService $taxService)
        {
            parent::__construct();
            $this->taxService = $taxService;
            $this->middleware( [ 'permission:settings' ] )->only( 'show' , 'store' , 'update' , 'destroy' );
        }

        public function index(PaginateRequest $request) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $methods = $this->filter( new Tax() , $request , [ 'name' ] );
                return TaxResource::collection( $methods );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(TaxRequest $request) : Application | Response | ResponseFactory | \Illuminate\Contracts\Foundation\Application | TaxResource
        {
            try {
                return new TaxResource( $this->taxService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(TaxRequest $request , Tax $tax) : Application | Response | ResponseFactory | \Illuminate\Contracts\Foundation\Application | TaxResource
        {
            try {
                return new TaxResource( $this->taxService->update( $request , $tax ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function deleteMethods(Request $request)
        {
            Tax::destroy( $request->get( 'ids' ) );
        }

        public function destroy(Tax $tax) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->taxService->destroy( $tax );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(Tax $tax) : Application | Response | ResponseFactory | \Illuminate\Contracts\Foundation\Application | TaxResource
        {
            try {
                return new TaxResource( $this->taxService->show( $tax ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
