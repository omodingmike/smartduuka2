<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseStatus;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Exports\StockExpiryExport;
    use App\Exports\StockExport;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StoreIngredientStockRequest;
    use App\Http\Resources\ExpiryStockResource;
    use App\Http\Resources\IngredientStockResource;
    use App\Http\Resources\RawStockResource;
    use App\Http\Resources\StockResource;
    use App\Http\Resources\StockTransferResource;
    use App\Models\Ingredient;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\Stock;
    use App\Services\StockService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\URL;
    use Maatwebsite\Excel\Facades\Excel;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class StockController extends AdminController
    {
        public StockService $stockService;
        protected           $stockFilter = [
            'name' ,
            'status' ,
        ];

        public function __construct(StockService $stockService)
        {
            parent::__construct();
            $this->stockService = $stockService;
            $this->middleware( [ 'permission:stock' ] )->only( 'index' , 'export' );
        }

        public function listIngredients(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                $stocks = Stock::with( 'item' )->where( 'status' , Status::ACTIVE )->where( function ($query) use ($requests) {
                    $query->where( 'model_type' , Ingredient::class );
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->stockFilter ) ) {
                            if ( $key == 'product_name' ) {
                                $query->whereHas( 'product' , function ($query) use ($request) {
                                    $query->where( 'name' , 'like' , '%' . $request . '%' );
                                } )->get();
                            }
                            else {
                                $query->where( $key , 'like' , '%' . $request . '%' );
                            }
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->get();

                if ( ! blank( $stocks ) ) {
                    $stocks->groupBy( 'product_id' )?->map( function ($item) {
                        $item->groupBy( 'product_id' )?->map( function ($item) {
//                    $stock_item = [
//                        'product_id'      => $item->first()['product_id'],
//                        'product_name' => $item->first()['item']['name'],
//                        'status'       => $item->first()['item']['status'],
//                        'itemStock'    => $item->sum('quantity'),
//                    ];
                        } );
                    } );
                    return $stocks;
                }
                else {
                    $this->items = [];
                }

                if ( $method == 'paginate' ) {
                    return $this->paginate( $this->items , $methodValue , NULL , URL::to( '/' ) . '/api/admin/itemStock' );
                }

                return $this->items;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function index(PaginateRequest $request)
        {
            try {
                return StockResource::collection( $this->stockService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function takings(PaginateRequest $request)
        {
            try {
                return RawStockResource::collection( $this->stockService->transfers( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function stockTransfers(Request $request)
        {
            try {
//                return $this->stockService->transfers($request);
                return RawStockResource::collection( $this->stockService->transfers( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function stockReconciliations(PaginateRequest $request)
        {
            try {
                return StockResource::collection( $this->stockService->transfers( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function expiryList(PaginateRequest $request)
        {
            try {
                return ExpiryStockResource::collection( $this->stockService->expiryList( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function cancelOrAccept(Request $request)
        {
            try {
                Stock::where( 'batch' , $request->batch )->update( [
                    'status' => $request->status
                ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function approveStockRequest(Request $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $products = json_decode( $request->products , TRUE );
                    foreach ( $products as $product ) {
                        Stock::where( [ 'batch' => $request->batch , 'product_id' => $product[ 'product_id' ] ] )->update( [
                            'status'           => StockStatus::APPROVED ,
                            'approve_quantity' => $product[ 'quantity' ] ,
                            'quantity'         => -$product[ 'quantity' ] ,
                        ] );
                    }
                } );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function receiveStockRequest(Request $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $products = json_decode( $request->products , TRUE );
                    foreach ( $products as $product ) {
                        $stock = Stock::where( [ 'batch' => $request->batch , 'product_id' => $product[ 'product_id' ] ] )->first();
                        $stock->update( [
                            'status'           => StockStatus::RECEIVED ,
                            'quantity'         => $product[ 'quantity' ] ,
                            'approve_quantity' => $product[ 'quantity' ] ,
                            'warehouse_id'     => $stock->destination_warehouse_id
                        ] );
                        Stock::where( [ 'warehouse_id' => $stock->source_warehouse_id , 'product_id' => $product[ 'product_id' ] ] )
                             ->decrement( 'quantity' , $product[ 'quantity' ] );
                    }
                } );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function showStockTransfer(Request $request)
        {
            try {
                return StockTransferResource::collection( $this->stockService->transfer( $request ) );
//                return response()->json([ 'data' => $this->stockService->transfer($request) ]);
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function storeIngredientStock(StoreIngredientStockRequest $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    $purchase = Purchase::create( [
                        'supplier_id'    => 1 ,
                        'date'           => now() ,
                        'reference_no'   => time() ,
                        'subtotal'       => 0 ,
                        'tax'            => 0 ,
                        'discount'       => 0 ,
                        'total'          => 0 ,
                        'note'           => $request->note ? $request->note : '' ,
                        'status'         => PurchaseStatus::RECEIVED ,
                        'payment_status' => PurchasePaymentStatus::FULLY_PAID
                    ] );

                    foreach ( $request->products as $product ) {
                        Stock::create( [
                            'model_type' => Purchase::class ,
                            'model_id'   => $purchase->id ,
                            'item_type'  => Ingredient::class ,
                            'product_id' => $product[ 'product_id' ] ,
                            'item_id'    => $product[ 'product_id' ] ,
                            'price'      => $product[ 'buying_price' ] ,
                            'quantity'   => $product[ 'quantity' ] ,
                            'discount'   => $product[ 'total_discount' ] ,
                            'tax'        => $product[ 'total_tax' ] ,
                            'subtotal'   => $product[ 'subtotal' ] ,
                            'total'      => $product[ 'total' ] ,
                            'status'     => Status::ACTIVE
                        ] );
                    }
                } );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function indexIngredients(PaginateRequest $request)
        {
            try {
//                return $this->stockService->listIngredients($request);
                return IngredientStockResource::collection( $this->stockService->listIngredients( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function storeItemStock(StoreIngredientStockRequest $request)
        {
//            try {
            foreach ( $request->products as $product ) {
                $stock = Stock::where( 'model_type' , Product::class )
                              ->where( 'model_id' , $product[ 'product_id' ] )
                              ->first();
                if ( $stock ) {
                    return $stock->increment( 'quantity' , $product[ 'quantity' ] );
                }
                else {
                    $stock = Stock::create( [
                        'model_type' => Product::class ,
                        'model_id'   => $product[ 'product_id' ] ,
                        'item_type'  => Product::class ,
                        'product_id' => $product[ 'product_id' ] ,
                        'price'      => $product[ 'price' ] ,
                        'quantity'   => $product[ 'quantity' ] ,
                        'discount'   => $product[ 'total_discount' ] ,
                        'tax'        => $product[ 'total_tax' ] ,
                        'subtotal'   => $product[ 'subtotal' ] ,
                        'total'      => $product[ 'total' ] ,
                        'status'     => Status::ACTIVE
                    ] );
                    return $stock;
                }
            }
//            } catch ( Exception $exception ) {
//                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
//            }
        }

        public function export(PaginateRequest $request) : Application | Response | BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return Excel::download( new StockExport( $this->stockService , $request ) , 'Stock.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function expiryReportExport(PaginateRequest $request) : Application | Response | BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
//            try {
            return Excel::download( new StockExpiryExport( $this->stockService , $request ) , 'Stock_Expiry_Report.xlsx' );
//            } catch ( Exception $exception ) {
//                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
//            }
        }

    }
