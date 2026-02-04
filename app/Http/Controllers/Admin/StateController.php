<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StateRequest;
    use App\Http\Resources\StateResource;
    use App\Http\Resources\StateSimpleResource;
    use App\Models\Country;
    use App\Models\State;
    use App\Services\StateService;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;


    class StateController extends AdminController
    {

        private StateService $stateService;

        public function __construct(StateService $state)
        {
            parent::__construct();
            $this->stateService = $state;
        }

        public function index(PaginateRequest $request)
        {
            try {
                return StateResource::collection( $this->stateService->list( $request ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function state(Request $request , Country $country)
        {
            try {
                $query = $country->states();
                $name  = $request->input( 'query' );
                if ( $name ) {
                    $query->where( 'name' , 'ilike' , "%$name%" );
                }
                return StateResource::collection( $query->take( 5 )->get() );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function simpleLists(PaginateRequest $request)
        {
            try {
                return StateSimpleResource::collection( $this->stateService->list( $request ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(StateRequest $request)
        {
            try {
                return new StateResource( $this->stateService->store( $request ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(StateRequest $request , State $state)
        {
            try {
                return new StateResource( $this->stateService->update( $request , $state ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(State $state)
        {
            try {
                $this->stateService->destroy( $state );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function statesByCountry(Country $country)
        {
            try {
                return StateResource::collection( $this->stateService->statesByCountry( $country ) );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
