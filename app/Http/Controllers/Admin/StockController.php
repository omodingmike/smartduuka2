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
    use App\Http\Resources\StockTakeResource;
    use App\Http\Resources\StockTransferResource;
    use App\Http\Resources\WastageResource;
    use App\Models\Ingredient;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\Stock;
    use App\Services\StockService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Maatwebsite\Excel\Facades\Excel;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class StockController extends AdminController
    {
        protected array $stockFilter = ['name', 'status'];

        public function __construct(protected StockService $stockService)
        {
            parent::__construct();
            $this->middleware(['permission:stock'])->only('index', 'export');
        }

        // ─── Helpers ────────────────────────────────────────────────────────────────

        /**
         * Return a standard 422 error response.
         */
        private function errorResponse(Exception $exception): JsonResponse
        {
            Log::error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }

        // ─── Read endpoints ──────────────────────────────────────────────────────────

        public function index(PaginateRequest $request)
        {
            try {
                $totalStockValue    = 0;
                $totalLowStockCount = 0;
                $stocks = $this->stockService->list($request, $totalStockValue, $totalLowStockCount);

                return StockResource::collection($stocks)->additional([
                    'meta' => [
                        'total_stock_value'     => currency($totalStockValue),
                        'total_low_stock_count' => $totalLowStockCount,
                    ],
                ]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function indexGroupedByBatch(PaginateRequest $request)
        {
            try {
                return StockResource::collection($this->stockService->listGroupedByBatch($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function indexIngredients(PaginateRequest $request)
        {
            try {
                return IngredientStockResource::collection($this->stockService->listIngredients($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function expiryList(PaginateRequest $request)
        {
            try {
                return ExpiryStockResource::collection($this->stockService->expiryList($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function wastage(PaginateRequest $request)
        {
            try {
                $totalLoss = 0;
                $wastage   = $this->stockService->wastage($request, $totalLoss);

                return WastageResource::collection($wastage)->additional([
                    'meta' => ['total_value_lost' => currency($totalLoss)],
                ]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function stockCapture(Request $request)
        {
            try {
                return StockTakeResource::collection($this->stockService->stockCapture($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function takings(Request $request)
        {
            try {
                return RawStockResource::collection($this->stockService->transfers($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function stockTransfers(Request $request)
        {
            try {
                return RawStockResource::collection($this->stockService->transfers($request) ?? collect());
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function stockReconciliations(PaginateRequest $request)
        {
            try {
                return StockResource::collection($this->stockService->transfers($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function showStockTransfer(Request $request)
        {
            try {
                return StockTransferResource::collection($this->stockService->transfer($request));
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function reconciliation(PaginateRequest $request)
        {
            try {
                // TODO: implement reconciliation logic
                return response()->json(['data' => []]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        // ─── Write endpoints ─────────────────────────────────────────────────────────

        /**
         * Update the status of every stock row sharing a batch.
         */
        public function cancelOrAccept(Request $request)
        {
            try {
                Stock::where('batch', $request->batch)->update(['status' => $request->status]);
                return response()->json(['status' => true]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        /**
         * Approve all products in a stock-transfer batch.
         */
        public function approveStockRequest(Request $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    $products = json_decode($request->products, true);

                    foreach ($products as $product) {
                        Stock::where([
                            'batch'      => $request->batch,
                            'product_id' => $product['product_id'],
                        ])->update([
                            'status'           => StockStatus::APPROVED,
                            'approve_quantity' => $product['quantity'],
                            'quantity'         => -$product['quantity'],
                        ]);
                    }
                });

                return response()->json(['status' => true]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        /**
         * Mark a stock-transfer batch as received and decrement source warehouse stock.
         */
        public function receiveStockRequest(Request $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    $products = json_decode($request->products, true);

                    foreach ($products as $product) {
                        /** @var Stock $stock */
                        $stock = Stock::where([
                            'batch'      => $request->batch,
                            'product_id' => $product['product_id'],
                        ])->firstOrFail();

                        $stock->update([
                            'status'           => StockStatus::RECEIVED,
                            'quantity'         => $product['quantity'],
                            'approve_quantity' => $product['quantity'],
                            'warehouse_id'     => $stock->destination_warehouse_id,
                        ]);

                        Stock::where([
                            'warehouse_id' => $stock->source_warehouse_id,
                            'product_id'   => $product['product_id'],
                        ])->decrement('quantity', $product['quantity']);
                    }
                });

                return response()->json(['status' => true]);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        /**
         * Create a Purchase record and stock entries for ingredient products.
         */
        public function storeIngredientStock(StoreIngredientStockRequest $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    $purchase = Purchase::create([
                        'supplier_id'    => 1,
                        'date'           => now(),
                        'reference_no'   => time(),
                        'subtotal'       => 0,
                        'tax'            => 0,
                        'discount'       => 0,
                        'total'          => 0,
                        'note'           => $request->note ?? '',
                        'status'         => PurchaseStatus::RECEIVED,
                        'payment_status' => PurchasePaymentStatus::FULLY_PAID,
                    ]);

                    $stockRows = array_map(fn($product) => [
                        'model_type' => Purchase::class,
                        'model_id'   => $purchase->id,
                        'item_type'  => Ingredient::class,
                        'product_id' => $product['product_id'],
                        'item_id'    => $product['product_id'],
                        'price'      => $product['buying_price'],
                        'quantity'   => $product['quantity'],
                        'discount'   => $product['total_discount'],
                        'tax'        => $product['total_tax'],
                        'subtotal'   => $product['subtotal'],
                        'total'      => $product['total'],
                        'status'     => Status::ACTIVE,
                    ], $request->products);

                    // Bulk insert instead of N individual inserts
                    Stock::insert($stockRows);
                });

                return response()->json(['status' => true], 201);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        /**
         * Upsert stock for product items (increment quantity if stock exists).
         */
        public function storeItemStock(StoreIngredientStockRequest $request)
        {
            try {
                DB::transaction(function () use ($request) {
                    foreach ($request->products as $product) {
                        $stock = Stock::where('model_type', Product::class)
                                      ->where('model_id', $product['product_id'])
                                      ->first();

                        if ($stock) {
                            $stock->increment('quantity', $product['quantity']);
                        } else {
                            Stock::create([
                                'model_type' => Product::class,
                                'model_id'   => $product['product_id'],
                                'item_type'  => Product::class,
                                'product_id' => $product['product_id'],
                                'price'      => $product['price'],
                                'quantity'   => $product['quantity'],
                                'discount'   => $product['total_discount'],
                                'tax'        => $product['total_tax'],
                                'subtotal'   => $product['subtotal'],
                                'total'      => $product['total'],
                                'status'     => Status::ACTIVE,
                            ]);
                        }
                    }
                });

                return response()->json(['status' => true], 201);
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        // ─── Export endpoints ────────────────────────────────────────────────────────

        public function export(PaginateRequest $request): Application|Response|BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|ResponseFactory
        {
            try {
                return Excel::download(new StockExport($this->stockService, $request), 'Stock.xlsx');
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }

        public function expiryReportExport(PaginateRequest $request): Application|Response|BinaryFileResponse|\Illuminate\Contracts\Foundation\Application|ResponseFactory
        {
            try {
                return Excel::download(new StockExpiryExport($this->stockService, $request), 'Stock_Expiry_Report.xlsx');
            } catch (Exception $exception) {
                return $this->errorResponse($exception);
            }
        }
    }