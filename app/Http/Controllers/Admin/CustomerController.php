<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\CustomerPaymentType;
    use App\Enums\CustomerWalletTransactionType;
    use App\Enums\PaymentType;
    use App\Enums\Role as EnumRole;
    use App\Exports\CustomerExport;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\CustomerPaymentRequest;
    use App\Http\Requests\CustomerRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\UserChangePasswordRequest;
    use App\Http\Resources\CustomerPaymentResource;
    use App\Http\Resources\CustomerResource;
    use App\Http\Resources\CustomerWalletTransactionResource;
    use App\Http\Resources\OrderResource;
    use App\Models\CustomerPayment;
    use App\Models\User;
    use App\Services\CustomerService;
    use App\Services\OrderService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
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
        }

        public function list(Request $request)
        {
            try {
                $customerQuery = $this->customerService->list( $request );

                $totalCredit = DB::query()
                                 ->fromSub( clone $customerQuery , 'sub' )
                                 ->sum( 'credits' );

                $customers = $customerQuery->get();

                return response()->json( [
                    'data' => $customers ,
                    'meta' => [
                        'total_credit' => currency( $totalCredit ) ,
                    ] ,
                ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function index(Request $request)
        {
            try {
                $customerQuery = $this->customerService->list( $request );
                $paginate      = $request->boolean( 'paginate' , TRUE );

                $totalCredit = DB::query()
                                 ->fromSub( clone $customerQuery , 'sub' )
                                 ->sum( 'credits' );

                if ( $paginate ) {
                    $customers = $customerQuery->paginate(
                        perPage: $request->integer( 'per_page' , 10 ) ,
                        page: $request->integer( 'page' , 1 )
                    );
                }
                else {
                    $customers = $customerQuery->get();
                }

                return CustomerResource::collection( $customers )->additional( [
                    'meta' => [
                        'total_credit' => currency( $totalCredit )
                    ]
                ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function posCustomers(Request $request)
        {
            try {
                $query = $request->input( 'query' );

                // -------------------------------------------------------------------------
                // OPTIMIZATION: Only select the columns the caller actually needs.
                // Previously this loaded full User models (all columns + relations) for
                // what is typically a lightweight POS customer-search dropdown.
                // -------------------------------------------------------------------------
                $customers = User::role( EnumRole::CUSTOMER )
                                 ->select( 'id' , 'name' , 'phone' , 'email' , 'status' )
                                 ->when( $query , fn($q) => $q->where( 'name' , 'ilike' , '%' . $query . '%' ) )
                                 ->orderByDesc( 'created_at' )
                                 ->get();

                return response()->json( [ 'data' => $customers ] );
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
        )
        {
            try {
                return new CustomerPaymentResource( $this->customerService->payment( $request , $customer ) );
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

        public function credits(User $customer) : Response
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

        public function debtPayments(Request $request)
        {
            $page     = $request->integer( 'page' , 1 );
            $per_page = $request->integer( 'per_page' , 15 );
            $payments = CustomerPayment::with( 'paymentMethod' )
                                       ->where( 'customer_payment_type' , CustomerPaymentType::DEBT )
                                       ->paginate( perPage: $per_page , page: $page );
            return CustomerPaymentResource::collection( $payments );
        }

        public function topUp(User $customer , Request $request)
        {
            $data = addToCustomerWalletTransaction(
                $customer ,
                $request->amount ,
                CustomerWalletTransactionType::DEPOSIT ,
                $request->payment_method_id ,
                $request->reference
            );
            return new CustomerWalletTransactionResource( $data );
        }

        public function simpleCustomers(Request $request)
        {
            try {
                return $this->customerService->simpleList( $request );
            } catch ( Exception $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }
    }