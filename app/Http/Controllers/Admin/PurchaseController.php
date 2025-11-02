<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\Constants;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;
    use App\Enums\Status;
    use App\Exports\PurchasesExport;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PurchasePaymentRequest;
    use App\Http\Requests\PurchaseRequest;
    use App\Http\Requests\StockReconcilliationRequest;
    use App\Http\Requests\StockTransferRequest;
    use App\Http\Requests\StorePosPaymentRequest;
    use App\Http\Resources\PaymentMethodResource;
    use App\Http\Resources\PurchaseDetailsResource;
    use App\Http\Resources\PurchasePaymentResource;
    use App\Http\Resources\PurchaseResource;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Models\RoyaltyCustomer;
    use App\Models\RoyaltyPointsExchageRate;
    use App\Models\RoyaltyPointsLog;
    use App\Services\ProductVariationService;
    use App\Services\PurchaseService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Maatwebsite\Excel\Facades\Excel;

    class PurchaseController extends AdminController
    {
        public PurchaseService         $purchaseService;
        public ProductVariationService $productVariationService;
        protected array                $purchaseFilter = [
            'supplier_id' ,
            'date' ,
            'reference_no' ,
            'status' ,
            'total' ,
            'note' ,
            'except'
        ];

        public function __construct(PurchaseService $purchaseService , ProductVariationService $productVariationService)
        {
            parent::__construct();
            $this->purchaseService         = $purchaseService;
            $this->productVariationService = $productVariationService;
            $this->middleware( [ 'permission:purchase' ] )->only( 'export' , 'downloadAttachment' );
            $this->middleware( [ 'permission:purchase_create' ] )->only( 'store' );
            $this->middleware( [ 'permission:purchase_edit' ] )->only( 'edit' , 'update' );
            $this->middleware( [ 'permission:purchase_delete' ] )->only( 'destroy' );
            $this->middleware( [ 'permission:purchase_show' ] )->only( 'show' );
        }

        public function storeIngredient(PurchaseRequest $request) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->storeIngredient( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function pos(StorePosPaymentRequest $request , Order $order) : object
        {
            try {
                DB::transaction( function () use ($request , $order) {
                    $payment_method = PaymentMethod::find( $request->payment_method );
                    $points         = $request->points;
                    if ( Str::contains( $payment_method->name , 'points' , TRUE ) ) {
                        $customer      = $order->user;
                        $exchange_rate = RoyaltyPointsExchageRate::first();
                        $points_value  = Constants::ROYALTY_POINTS_DEFAULT_VALUE;
                        if ( $exchange_rate ) {
                            $points_value = $exchange_rate->value / $exchange_rate->points;
                        }
                        $amount = $points * $points_value;
                        if ( $customer instanceof RoyaltyCustomer ) {
                            $customer->decrement( 'points' , $points );
                            RoyaltyPointsLog::create( [
                                'customer_id' => $customer->id ,
                                'points'      => $points ,
                                'type'        => 'Redeemed Points' ,
                                'redeemed_by' => auth()->id()
                            ] );
                        }
                    }
                    else {
                        $amount = $request->amount;
                    }
                    $purchasePayment       = PosPayment::create( [
                        'order_id'       => $order->id ,
                        'date'           => date( 'Y-m-d H:i:s' , strtotime( $request->date ) ) ,
                        'reference_no'   => $request->reference_no ,
                        'amount'         => $amount ,
                        'payment_method' => $request->payment_method ,
                    ] );
                    $order->payment_method = $request->payment_method;
                    $order->change         = $request->change;
                    if ( $order->paid == NULL ) {
                        $order->paid = $amount;
                    }
                    else {
                        $order->increment( 'paid' , $amount );
                    }

                    if ( $request->file ) {
                        $purchasePayment->addMediaFromRequest( 'file' )->toMediaCollection( 'pos_payment' );
                    }
                    if ( $request->payment_file ) {
                        $purchasePayment->addMediaFromRequest( 'payment_file' )->toMediaCollection( 'pos_payment' );
                    }

                    $checkPosPayment = PosPayment::where( 'order_id' , $order->id )->sum( 'amount' );

                    if ( $checkPosPayment == $order->total ) {
                        $order->diningTable()->update( [ 'status' => Status::AVAILABLE ] );
                        $order->payment_status = PaymentStatus::PAID;
                        $order->status         = OrderStatus::COMPLETED;
                    }

                    if ( $checkPosPayment < $order->total ) {
                        $order->payment_status = PaymentStatus::UNPAID;
                    }
                    $order->save();
                } );
                return $order;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function paymentMethods()
        {
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function ingreidentList(PaginateRequest $request)
        {

            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return Purchase::with( 'supplier' )->where( function ($query) use ($requests) {
                    $query->where( 'type' , $requests[ 'type' ] );
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->purchaseFilter ) ) {
                            if ( $key == 'except' ) {
                                $explodes = explode( '|' , $request );
                                if ( count( $explodes ) ) {
                                    foreach ( $explodes as $explode ) {
                                        $query->where( 'id' , '!=' , $explode );
                                    }
                                }
                            }
                            else {
                                if ( $key == 'supplier_id' || $key == 'status' ) {
                                    $query->where( $key , $request );
                                }
                                else if ( $key == 'date' && ! empty( $request ) ) {
                                    $date_start = date( 'Y-m-d 00:00:00' , strtotime( $request ) );
                                    $date_end   = date( 'Y-m-d 23:59:59' , strtotime( $request ) );
                                    $query->where( $key , '>=' , $date_start )->where( $key , '<=' , $date_end );
                                }
                                else {
                                    $query->where( $key , 'like' , '%' . $request . '%' );
                                }
                            }
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method( $methodValue );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function indexIngredients(PaginateRequest $request) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return PurchaseResource::collection( $this->purchaseService->ingreidentList( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function index(PaginateRequest $request) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return PurchaseResource::collection( $this->purchaseService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function storeStock(PurchaseRequest $request) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->storeStock( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function transferStock(StockTransferRequest $request) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->transferStock( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function reconcileStock(StockReconcilliationRequest $request) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->reconcileStock( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(PurchaseRequest $request) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(Purchase $purchase) : Application | Response | PurchaseDetailsResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->show( $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function showIngredient(Purchase $purchase) : Application | Response | PurchaseDetailsResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->showIngredient( $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function edit(Purchase $purchase) : Application | Response | PurchaseDetailsResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->edit( $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(PurchaseRequest $request , Purchase $purchase) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->update( $request , $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Purchase $purchase) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->purchaseService->destroy( $purchase );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function export(PaginateRequest $request) : Application | Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return Excel::download( new PurchasesExport( $this->purchaseService , $request ) , 'Purchases.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function downloadAttachment(Purchase $purchase)
        {
            try {
                return $this->purchaseService->downloadAttachment( $purchase );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function payment(PurchasePaymentRequest $request , Purchase $purchase) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->payment( $request , $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function paymentHistory(int $type , Purchase $purchase) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return PurchasePaymentResource::collection( $this->purchaseService->paymentHistory( $type , $purchase ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function paymentDownloadAttachment(PurchasePayment $purchasePayment)
        {
            try {
                return $this->purchaseService->paymentDownloadAttachment( $purchasePayment );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function paymentDestroy(int $type , Purchase $purchase , PurchasePayment $purchasePayment) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->purchaseService->paymentDestroy( $purchase , $purchasePayment , $type );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
