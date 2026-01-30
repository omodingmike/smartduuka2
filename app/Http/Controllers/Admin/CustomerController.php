<?php

    namespace App\Http\Controllers\Admin;

    use App\Exports\CustomerExport;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\CustomerPaymentRequest;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Http\Resources\CustomerResource;
    use App\Http\Resources\OrderResource;
    use App\Models\User;
    use App\Services\CustomerService;
    use App\Services\OrderService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class CustomerController extends AdminController
    {
        private CustomerService $customerService;
        private OrderService    $orderService;

        public function __construct(CustomerService $customerService , OrderService $orderService)
        {
            parent::__construct();
            $this->customerService = $customerService;
            $this->orderService    = $orderService;
            $this->middleware( [ 'permission:customers' ] )->only(
                'index' ,
                'export' ,
                'changePassword' ,
                'changeImage' ,
                'myOrder'
            );
            $this->middleware( [ 'permission:customers_create' ] )->only( 'store' );
            $this->middleware( [ 'permission:customers_edit' ] )->only( 'update' );
            $this->middleware( [ 'permission:customers_delete' ] )->only( 'destroy' );
            $this->middleware( [ 'permission:customers_show' ] )->only( 'show' );
        }

        public function index(PaginateRequest $request
        ) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return CustomerResource::collection( $this->customerService->list( $request ) );
            } catch ( Exception $exception ) {

                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );

            }
        }

        public function store(CustomerRequest $request
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(
            CustomerRequest $request ,
            User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->update( $request , $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function payment(
            CustomerPaymentRequest $request ,
            User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->payment( $request , $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            } catch ( \Throwable $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                User::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->show( $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function credits(User $customer
        ) : Response
        {
            try {
                return response( [ 'data' => $customer->credits ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }


        public function export(PaginateRequest $request
        ) : Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
        {
            try {
                return Excel::download( new CustomerExport( $this->customerService , $request ) , 'Customer.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changePassword(
            UserChangePasswordRequest $request ,
            User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->changePassword( $request , $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changeImage(
            ChangeImageRequest $request ,
            User $customer
        ) : Response | CustomerResource | Application | ResponseFactory
        {
            try {
                return new CustomerResource( $this->customerService->changeImage( $request , $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function myOrder(
            PaginateRequest $request ,
            User $customer
        ) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->userOrder( $request , $customer ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
