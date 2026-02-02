<?php

    namespace App\Http\Controllers\Admin;

    use App\Exports\AdministratorExport;
    use App\Http\Requests\AdministratorRequest;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Http\Resources\AdministratorResource;
    use App\Http\Resources\OrderResource;
    use App\Jobs\SendUserCredentialsJob;
    use App\Models\User;
    use App\Services\AdministratorService;
    use App\Services\OrderService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class AdministratorController extends AdminController
    {
        private AdministratorService $administratorService;
        private OrderService         $orderService;


        public function __construct(AdministratorService $administratorService , OrderService $orderService)
        {
            parent::__construct();
            $this->administratorService = $administratorService;
            $this->orderService         = $orderService;
//            $this->middleware( [ 'permission:administrators' ] )->only( 'index' , 'export' );
//            $this->middleware( [ 'permission:administrators_create' ] )->only( 'store' );
//            $this->middleware( [ 'permission:administrators_edit' ] )->only( 'update' );
//            $this->middleware( [ 'permission:administrators_delete' ] )->only( 'destroy' );
//            $this->middleware( [ 'permission:administrators_show' ] )->only( 'show' , 'changePassword' , 'changeImage' , 'myOrder' );
        }

        public function index(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return AdministratorResource::collection( $this->administratorService->list( $request ) );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(AdministratorRequest $request) : AdministratorResource | Response | Application | ResponseFactory
        {
            try {
                $user = $this->administratorService->store( $request );
                if ( $request->boolean( 'emailCredentials' ) ) {
                    SendUserCredentialsJob::dispatch( $user , $request->password, $request->pin );
                }
                return new AdministratorResource( $user );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(AdministratorRequest $request , User $administrator) : AdministratorResource | Response | Application | ResponseFactory
        {
            try {
                $user = $this->administratorService->update( $request , $administrator );
                if ( $request->boolean( 'emailCredentials' ) && $request->filled( 'password' ) ) {
                    SendUserCredentialsJob::dispatch( $user , $request->password, $request->pin );
                }
                return new AdministratorResource( $user );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                User::destroy( $request->ids);;
                return response( '' , 202 );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(User $administrator) : AdministratorResource | Response | Application | ResponseFactory
        {
            try {
                return new AdministratorResource( $this->administratorService->show( $administrator ) );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function export(PaginateRequest $request) : Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
        {
            try {
                return Excel::download( new AdministratorExport( $this->administratorService , $request ) , 'Administrator.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changePassword(UserChangePasswordRequest $request , User $administrator) : AdministratorResource | Response | Application | ResponseFactory
        {
            try {
                return new AdministratorResource( $this->administratorService->changePassword( $request , $administrator ) );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changeImage(ChangeImageRequest $request , User $administrator) : AdministratorResource | Response | Application | ResponseFactory
        {
            try {
                return new AdministratorResource( $this->administratorService->changeImage( $request , $administrator ) );
            } catch ( \Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function myOrder(PaginateRequest $request , User $administrator) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->userOrder( $request , $administrator ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
