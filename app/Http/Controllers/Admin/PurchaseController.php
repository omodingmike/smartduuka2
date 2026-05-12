<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\Constants;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;
    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseRequestStatus;
    use App\Enums\PurchaseStatus;
    use App\Enums\StockStatus;
    use App\Exports\PurchasesExport;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PurchasePaymentRequest;
    use App\Http\Requests\PurchaseRequest;
    use App\Http\Requests\StockPurchaseRequestRequest;
    use App\Http\Requests\StockReconcilliationRequest;
    use App\Http\Requests\StockTransferRequest;
    use App\Http\Requests\StorePosPaymentRequest;
    use App\Http\Resources\PaymentMethodResource;
    use App\Http\Resources\PurchaseDetailsResource;
    use App\Http\Resources\PurchasePaymentResource;
    use App\Http\Resources\PurchaseResource;
    use App\Http\Resources\StockPurchaseRequestResource;
    use App\Http\Resources\TaxResource;
    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\PosPayment;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Models\RoyaltyCustomer;
    use App\Models\RoyaltyPointsExchageRate;
    use App\Models\RoyaltyPointsLog;
    use App\Models\Stock;
    use App\Models\StockPurchaseRequest;
    use App\Models\Tax;
    use App\Models\Warehouse;
    use App\Services\ProductVariationService;
    use App\Services\PurchaseService;
    use App\Traits\SaveMedia;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Maatwebsite\Excel\Facades\Excel;

    class PurchaseController extends AdminController
    {
        use SaveMedia;

        protected array $purchaseFilter = [
            'supplier_id' ,
            'date' ,
            'reference_no' ,
            'status' ,
            'total' ,
            'note' ,
            'except' ,
        ];

        public function __construct(
            protected PurchaseService $purchaseService ,
            protected ProductVariationService $productVariationService ,
        )
        {
            parent::__construct();

            // Uncomment and adjust permissions as needed:
            // $this->middleware(['permission:purchase'])->only('export', 'downloadAttachment');
            // $this->middleware(['permission:purchase_create'])->only('store');
            // $this->middleware(['permission:purchase_edit'])->only('edit', 'update');
            // $this->middleware(['permission:purchase_delete'])->only('destroy');
            // $this->middleware(['permission:purchase_show'])->only('show');
        }

        // ─── Helpers ─────────────────────────────────────────────────────────────────

        /**
         * Return a standard 422 error response and log the exception.
         */
        private function errorResponse(Exception $exception) : JsonResponse
        {
            Log::error( $exception->getMessage() , [ 'trace' => $exception->getTraceAsString() ] );
            return response()->json( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
        }

        // ─── Lookup endpoints ────────────────────────────────────────────────────────

        public function paymentMethods() : AnonymousResourceCollection
        {
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function taxes() : AnonymousResourceCollection
        {
            return TaxResource::collection( Tax::all() );
        }

        // ─── List endpoints ──────────────────────────────────────────────────────────

        public function index(PaginateRequest $request)
        {
            try {
                return PurchaseResource::collection( $this->purchaseService->list( $request ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function indexIngredients(PaginateRequest $request)
        {
            try {
                return PurchaseResource::collection( $this->purchaseService->ingreidentList( $request ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function listRequest(PaginateRequest $request)
        {
            try {
                return StockPurchaseRequestResource::collection( $this->purchaseService->listRequest( $request ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        /**
         * Ingredient purchase list (used by older routes that pass a 'type' filter directly).
         */
        public function ingreidentList(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' , 'id' );
                $orderType   = $request->get( 'order_type' , 'desc' );

                return Purchase::with( 'supplier' )
                               ->where( function ($query) use ($requests) {
                                   $query->where( 'type' , $requests[ 'type' ] );

                                   foreach ( $requests as $key => $value ) {
                                       if ( ! in_array( $key , $this->purchaseFilter ) ) {
                                           continue;
                                       }
                                       $this->applyPurchaseFilter( $query , $key , $value );
                                   }
                               } )
                               ->orderBy( $orderColumn , $orderType )
                               ->$method( $methodValue );
            } catch ( Exception $exception ) {
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        // ─── CRUD endpoints ──────────────────────────────────────────────────────────

        public function store(PurchaseRequest $request)
        {
            try {
                return new PurchaseResource( $this->purchaseService->store( $request ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function storeIngredient(PurchaseRequest $request)
        {
            try {
                return new PurchaseResource( $this->purchaseService->storeIngredient( $request ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function storeStock(PurchaseRequest $request)
        {
            try {
                return $this->purchaseService->storeStock( $request );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function show(Purchase $purchase)
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->show( $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function showIngredient(Purchase $purchase)
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->showIngredient( $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function edit(Purchase $purchase)
        {
            try {
                return new PurchaseDetailsResource( $this->purchaseService->edit( $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function update(PurchaseRequest $request , Purchase $purchase)
        {
            try {
                return new PurchaseResource( $this->purchaseService->update( $request , $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function destroy(Request $request)
        {
            try {
                Purchase::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        // ─── Transfer & reconciliation endpoints ─────────────────────────────────────

        public function transferStock(StockTransferRequest $request)
        {
            try {
                return $this->purchaseService->transferStock( $request );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function reconcileStock(StockReconcilliationRequest $request)
        {
            try {
                return $this->purchaseService->reconcileStock( $request );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function receive(Request $request)
        {
            try {
                return $this->purchaseService->receive( $request );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        // ─── Purchase request endpoints ───────────────────────────────────────────────

        public function request(StockPurchaseRequestRequest $request)
        {
            try {
                return $this->purchaseService->request( $request );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        /**
         * Change the status of a StockPurchaseRequest.
         * When status transitions to ORDERED, creates a Purchase and stock rows in a transaction.
         */
        public function changeRequestStatus(Request $request , StockPurchaseRequest $purchase)
        {
            try {
                DB::transaction( function () use ($purchase , $request) {
                    $purchase->update( [ 'status' => $request->status ] );

                    if ( (int) $request->status !== PurchaseRequestStatus::ORDERED->value ) {
                        return;
                    }

                    $warehouseId = Warehouse::value( 'id' ); // single query instead of first()->id
                    $products    = $purchase->stocks->map( function (Stock $stock) {
                        return [
                            'stock_id'         => $stock->id ,
                            'product_id'       => $stock->product_id ,
                            'product_name'     => $stock->product->name ,
                            'price'            => $stock->price ,
                            'quantity_ordered' => $stock->quantity_ordered ,
                            'currency_price'   => AppLibrary::currencyAmountFormat( $stock->price ) ,
                            'total'            => $stock->total ,
                            'total_currency'   => AppLibrary::currencyAmountFormat( $stock->total ) ,
                            'quantity'         => $stock->quantity ,
                            'unit'             => $stock->product->unit->short_name ,
                        ];
                    } );

                    $total = $products->sum( 'total' );

                    $p = Purchase::create( [
                        'date'           => now() ,
                        'reference_no'   => 'PO' . time() ,
                        'subtotal'       => $total ,
                        'total'          => $total ,
                        'notes'          => $purchase->reason ,
                        'status'         => PurchaseStatus::PENDING ,
                        'shipping'       => 0 ,
                        'payment_status' => PurchasePaymentStatus::PENDING ,
                        'warehouse_id'   => $warehouseId ,
                        'supplier_id'    => $purchase->supplier_id ,
                    ] );

                    $stockRows = $products->map( fn($product) => [
                        'model_type'       => Purchase::class ,
                        'reference'        => 'S' . time() ,
                        'model_id'         => $p->id ,
                        'expiry_date'      => NULL ,
                        'item_type'        => Product::class ,
                        'product_id'       => $product[ 'product_id' ] ,
                        'item_id'          => $product[ 'product_id' ] ,
                        'variation_names'  => NULL ,
                        'price'            => $product[ 'price' ] ,
                        'quantity'         => 0 ,
                        'quantity_ordered' => $product[ 'quantity_ordered' ] ,
                        'discount'         => 0 ,
                        'tax'              => 0 ,
                        'subtotal'         => $product[ 'total' ] ,
                        'total'            => $product[ 'total' ] ,
                        'sku'              => NULL ,
                        'warehouse_id'     => $warehouseId ,
                        'status'           => StockStatus::IN_TRANSIT ,
                    ] )->all();

                    // Bulk insert instead of N individual inserts
                    Stock::insert( $stockRows );
                } );

                return response()->json( [] , 200 );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        // ─── POS endpoint ────────────────────────────────────────────────────────────

        /**
         * Record a POS payment, handle royalty point redemption, and update order status.
         */
        public function pos(StorePosPaymentRequest $request , Order $order) : object
        {
            try {
                DB::transaction( function () use ($request , $order) {
                    $paymentMethod = PaymentMethod::findOrFail( $request->payment_method );
                    $amount        = $request->amount;

                    if ( Str::contains( $paymentMethod->name , 'points' , TRUE ) ) {
                        $points       = $request->points;
                        $exchangeRate = RoyaltyPointsExchageRate::first();
                        $pointsValue  = $exchangeRate
                            ? ( $exchangeRate->value / $exchangeRate->points )
                            : Constants::ROYALTY_POINTS_DEFAULT_VALUE;
                        $amount       = $points * $pointsValue;

                        if ( $order->user instanceof RoyaltyCustomer ) {
                            $order->user->decrement( 'points' , $points );
                            RoyaltyPointsLog::create( [
                                'customer_id' => $order->user->id ,
                                'points'      => $points ,
                                'type'        => 'Redeemed Points' ,
                                'redeemed_by' => auth()->id() ,
                            ] );
                        }
                    }

                    $posPayment = PosPayment::create( [
                        'order_id'       => $order->id ,
                        'date'           => now()->parse( $request->date )->format( 'Y-m-d H:i:s' ) ,
                        'reference_no'   => $request->reference_no ,
                        'amount'         => $amount ,
                        'payment_method' => $request->payment_method ,
                    ] );

                    $order->payment_method = $request->payment_method;
                    $order->change         = $request->change;
                    $order->paid === NULL
                        ? $order->paid = $amount
                        : $order->increment( 'paid' , $amount );

                    if ( $request->hasFile( 'file' ) ) {
                        $posPayment->addMediaFromRequest( 'file' )->toMediaCollection( 'pos_payment' );
                    }
                    if ( $request->hasFile( 'payment_file' ) ) {
                        $posPayment->addMediaFromRequest( 'payment_file' )->toMediaCollection( 'pos_payment' );
                    }

                    $totalPaid = PosPayment::where( 'order_id' , $order->id )->sum( 'amount' );

                    $order->payment_status = match ( TRUE ) {
                        $totalPaid >= $order->total => PaymentStatus::PAID ,
                        default                     => PaymentStatus::UNPAID ,
                    };

                    if ( $totalPaid >= $order->total ) {
                        $order->status = OrderStatus::COMPLETED;
                    }

                    $order->save();
                } );

                return $order;
            } catch ( Exception $exception ) {
                DB::rollBack();
                Log::error( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        // ─── Payment endpoints ───────────────────────────────────────────────────────

        public function payment(PurchasePaymentRequest $request , Purchase $purchase) : Application | Response | PurchaseResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new PurchaseResource( $this->purchaseService->payment( $request , $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function paymentHistory(int $type , Purchase $purchase) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return PurchasePaymentResource::collection( $this->purchaseService->paymentHistory( $type , $purchase ) );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function paymentDownloadAttachment(PurchasePayment $purchasePayment)
        {
            try {
                return $this->purchaseService->paymentDownloadAttachment( $purchasePayment );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function paymentDestroy(int $type , Purchase $purchase , PurchasePayment $purchasePayment) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->purchaseService->paymentDestroy( $purchase , $purchasePayment , $type );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        // ─── Export / download endpoints ─────────────────────────────────────────────

        public function export(PaginateRequest $request) : Application | Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return Excel::download( new PurchasesExport( $this->purchaseService , $request ) , 'Purchases.xlsx' );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        public function downloadAttachment(Purchase $purchase)
        {
            try {
                return $this->purchaseService->downloadAttachment( $purchase );
            } catch ( Exception $exception ) {
                return $this->errorResponse( $exception );
            }
        }

        // ─── Internal helpers ────────────────────────────────────────────────────────

        /**
         * Apply a single purchase filter clause to the query.
         */
        private function applyPurchaseFilter($query , string $key , mixed $value) : void
        {
            match ( $key ) {
                'except'                 => collect( explode( '|' , $value ) )
                    ->each( fn($id) => $query->where( 'id' , '!=' , $id ) ) ,

                'supplier_id' , 'status' => $query->where( $key , $value ) ,

                'date'                   => ! empty( $value ) ? $query->whereDate( $key , $value ) : NULL ,

                default                  => $query->where( $key , 'like' , '%' . $value . '%' ) ,
            };
        }
    }