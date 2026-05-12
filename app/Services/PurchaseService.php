<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\ExpenseType;
    use App\Enums\PreOrderStatus;
    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseStatus;
    use App\Enums\PurchaseType;
    use App\Enums\Status;
    use App\Enums\StockReconciliationType;
    use App\Enums\StockStatus;
    use App\Enums\StockType;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PurchasePaymentRequest;
    use App\Http\Requests\PurchaseRequest;
    use App\Http\Requests\StockPurchaseRequestRequest;
    use App\Http\Requests\StockReconcilliationRequest;
    use App\Http\Requests\StockTransferRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\Expense;
    use App\Models\ExpenseCategory;
    use App\Models\Ingredient;
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\ProductAttributeOption;
    use App\Models\ProductVariation;
    use App\Models\Purchase;
    use App\Models\PurchasePayment;
    use App\Models\Stock;
    use App\Models\StockPurchaseRequest;
    use App\Models\StockTax;
    use App\Models\Tax;
    use App\Models\Warehouse;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class PurchaseService
    {
        /**
         * Singleton references used across transaction closures.
         * Using typed properties avoids uninitialized-access bugs.
         */
        protected ?Purchase $purchase = null;
        protected ?Stock    $stock    = null;

        protected array $purchaseFilter = [
            'supplier_id',
            'date',
            'reference_no',
            'status',
            'total',
            'note',
            'except',
        ];

        // ─── Shared filter helper ────────────────────────────────────────────────────

        /**
         * Apply a single filter key/value to a query builder.
         * Extracted to eliminate the identical foreach blocks duplicated across list methods.
         */
        private function applyPurchaseFilter($query, string $key, mixed $value): void
        {
            match ($key) {
                'except'               => collect(explode('|', $value))
                    ->each(fn($id) => $query->where('id', '!=', (int) $id)),

                'supplier_id', 'status' => $query->where($key, $value),

                'date'                 => !empty($value)
                    ? $query->whereDate($key, $value)
                    : null,

                default                => $query->where($key, 'like', '%' . $value . '%'),
            };
        }

        /**
         * Apply the standard filter set to a query builder.
         */
        private function applyFilters($query, array $requests): void
        {
            foreach ($requests as $key => $value) {
                if (in_array($key, $this->purchaseFilter)) {
                    $this->applyPurchaseFilter($query, $key, $value);
                }
            }
        }

        // ─── Listing methods ─────────────────────────────────────────────────────────

        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
                $orderColumn = $request->get('order_column', 'id');
                $orderType   = $request->get('order_type', 'desc');

                return Purchase::with([
                    'supplier',
                    'creator',
                    'purchasePayments',
                    'retailPriceUpdates',
                    'wholesalePriceUpdates',
                    'stocks.product',
                ])
                               ->where(function ($query) use ($requests) {
                                   $this->applyFilters($query, $requests);
                               })
                               ->orderBy($orderColumn, $orderType)
                               ->$method($methodValue);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function listRequest(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
                $orderColumn = $request->get('order_column', 'id');
                $orderType   = $request->get('order_type', 'desc');

                return StockPurchaseRequest::with(['stocks.product'])
                                           ->where(function ($query) use ($requests) {
                                               $this->applyFilters($query, $requests);
                                           })
                                           ->orderBy($orderColumn, $orderType)
                                           ->$method($methodValue);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function ingreidentList(PaginateRequest $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
                $orderColumn = $request->get('order_column', 'id');
                $orderType   = $request->get('order_type', 'desc');

                return Purchase::with('supplier')
                               ->where(function ($query) use ($requests) {
                                   $query->where('type', PurchaseType::STOCK_PURCHASE);
                                   $this->applyFilters($query, $requests);
                               })
                               ->orderBy($orderColumn, $orderType)
                               ->$method($methodValue);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Store methods ───────────────────────────────────────────────────────────

        /**
         * @throws Exception
         */
        public function store(PurchaseRequest $request): object
        {
            try {
                DB::transaction(function () use ($request) {
                    $warehouseId = Warehouse::value('id'); // avoids loading a full model
                    $status      = $request->integer('status');
                    $shipping    = $request->integer('shipping');

                    $this->purchase = Purchase::create([
                        'supplier_id'    => $request->supplier_id,
                        'date'           => $request->date,
                        'reference_no'   => $request->reference_no,
                        'subtotal'       => $request->subtotal,
                        'total'          => $request->total,
                        'notes'          => $request->note ?? '',
                        'status'         => $status,
                        'shipping'       => $shipping,
                        'payment_status' => PurchasePaymentStatus::PENDING->value,
                        'warehouse_id'   => $warehouseId,
                        'tax'            => 0,
                        'discount'       => 0,
                    ]);

                    activity()->log('Created Purchase with id: ' . $this->purchase->id);

                    if ($request->items) {
                        $products = json_decode($request->items, true);

                        foreach ($products as $product) {
                            $expiryDate   = isset($product['expiry']) ? Carbon::parse($product['expiry'])->endOfDay() : null;
                            $qty          = ($status === PurchaseStatus::RECEIVED->value) ? $product['quantity'] : 0;
                            $stockStatus  = ($status === PurchaseStatus::RECEIVED->value)
                                ? StockStatus::RECEIVED->value
                                : StockStatus::IN_TRANSIT->value;

                            Stock::create([
                                'model_type'      => Purchase::class,
                                'reference'       => 'S' . time(),
                                'model_id'        => $this->purchase->id,
                                'expiry_date'     => $expiryDate,
                                'item_type'       => Product::class,
                                'product_id'      => $product['product_id'],
                                'item_id'         => $product['product_id'],
                                'variation_names' => null,
                                'price'           => $product['price'],
                                'quantity'        => $qty,
                                'discount'        => 0,
                                'tax'             => 0,
                                'subtotal'        => $product['price'] * $product['quantity'],
                                'total'           => $product['price'] * $product['quantity'],
                                'sku'             => null,
                                'warehouse_id'    => $warehouseId,
                                'status'          => $stockStatus,
                            ]);

                            $productModel = Product::findOrFail($product['product_id']);
                            $productModel->update([
                                'buying_price'  => $product['price'],
                                'selling_price' => $product['retailPrices'][0]['new_price'] ?? $productModel->selling_price,
                            ]);

                            $this->upsertRetailPrices($productModel, $product);
                            $this->upsertWholesalePrices($productModel, $product);
                            $this->updatePreOrders($productModel);
                        }
                    }
                });

                return $this->purchase;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * @throws Exception
         */
        public function storeIngredient(PurchaseRequest $request): object
        {
            try {
                DB::transaction(function () use ($request) {
                    $purchase = Purchase::create([
                        'supplier_id'    => $request->supplier_id,
                        'date'           => Carbon::parse($request->date)->format('Y-m-d H:i:s'),
                        'reference_no'   => $request->reference_no,
                        'subtotal'       => $request->subtotal,
                        'tax'            => $request->tax,
                        'type'           => PurchaseType::STOCK_PURCHASE,
                        'discount'       => $request->discount,
                        'balance'        => $request->amount ?? $request->total,
                        'total'          => $request->total,
                        'note'           => $request->note ?? '',
                        'status'         => $request->status,
                        'payment_status' => PurchasePaymentStatus::PENDING,
                    ]);

                    $this->purchase  = $purchase;
                    $purchasePayment = null;

                    if ($request->add_payment == Ask::YES) {
                        $purchasePayment = PurchasePayment::create([
                            'purchase_id'    => $purchase->id,
                            'date'           => Carbon::parse($request->payment_date)->format('Y-m-d H:i:s'),
                            'reference_no'   => $request->reference_no,
                            'amount'         => $request->amount,
                            'payment_method' => $request->payment_method,
                        ]);
                    }

                    if ($request->products) {
                        $products = json_decode($request->products, true);

                        foreach ($products as $product) {
                            $existing = Stock::where('model_type', Ingredient::class)
                                             ->where('model_id', $product['product_id'])
                                             ->first();

                            if ($existing) {
                                $existing->increment('quantity', $product['quantity']);
                            } else {
                                Stock::create([
                                    'model_type' => Purchase::class,
                                    'model_id'   => $purchase->id,
                                    'item_type'  => Ingredient::class,
                                    'product_id' => $product['product_id'],
                                    'item_id'    => $product['product_id'],
                                    'price'      => $product['price'],
                                    'type'       => PurchaseType::STOCK_PURCHASE,
                                    'quantity'   => $product['quantity'],
                                    'discount'   => $product['total_discount'],
                                    'tax'        => $product['total_tax'],
                                    'subtotal'   => $product['subtotal'],
                                    'total'      => $product['total'],
                                    'status'     => $request->status == PurchaseStatus::RECEIVED
                                        ? Status::ACTIVE
                                        : Status::INACTIVE,
                                ]);
                            }
                        }
                    }

                    if ($request->hasFile('file')) {
                        $this->purchase->addMediaFromRequest('file')->toMediaCollection('purchase');
                    }

                    if ($purchasePayment && $request->hasFile('payment_file')) {
                        $purchasePayment->addMediaFromRequest('payment_file')->toMediaCollection('purchase_payment');
                    }

                    if ($request->add_payment == Ask::YES) {
                        $paid = PurchasePayment::where([
                            'purchase_id'   => $purchase->id,
                            'purchase_type' => PurchaseType::STOCK_PURCHASE,
                        ])->sum('amount');

                        $purchase->payment_status = match (true) {
                            $paid >= $purchase->total => PurchasePaymentStatus::FULLY_PAID,
                            $paid > 0                 => PurchasePaymentStatus::PARTIAL_PAID,
                            default                   => PurchasePaymentStatus::PENDING,
                        };
                        $purchase->save();
                    }
                });

                activityLog('Purchased Raw materials');
                return $this->purchase;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Receive stock items against a purchase request.
         */
        public function receive(Request $request): array
        {
            try {
                DB::transaction(function () use ($request) {
                    if (!$request->items) return;

                    $products = json_decode($request->items, true);

                    foreach ($products as $product) {
                        $stock = Stock::findOrFail($product['stock_id']);
                        $stock->increment('quantity_received', $product['quantity_received']);
                        $stock->increment('quantity', $product['quantity_received']);
                        $stock->update(['status' => StockStatus::RECEIVED]);
                    }
                });

                return [];
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Stock operations ────────────────────────────────────────────────────────

        /**
         * Store stock directly (without a formal purchase order).
         */
        public function storeStock(PurchaseRequest $request)
        {
            try {
                $warehouseId = $request->input('warehouse_id');
                $products    = json_decode($request->string('products'), true);
                $batch       = 'B' . time();

                foreach ($products as $p) {
                    $product        = Product::findOrFail($p['product_id']);
                    $variationId    = $p['variation_id'] ?? null;
                    $variationNames = '';

                    $expiryDate = isset($p['expiry']) ? Carbon::parse($p['expiry'])->endOfDay() : null;

                    if ($variationId) {
                        $targetModel = ProductVariation::findOrFail($variationId);
                        $targetClass = ProductVariation::class;
                        $price       = $targetModel->price ?? $product->buying_price;
                        $sku         = $targetModel->sku ?? $product->sku;

                        if (!empty($p['variation_path'])) {
                            ksort($p['variation_path']);
                            $names = [];
                            // Bulk-load options to avoid N+1
                            $options = ProductAttributeOption::with('productAttribute')
                                                             ->whereIn('id', array_values($p['variation_path']))
                                                             ->get()
                                                             ->keyBy('id');

                            foreach ($p['variation_path'] as $optionId) {
                                $option = $options->get($optionId);
                                if ($option && $option->productAttribute) {
                                    $names[] = $option->productAttribute->name . ' :: ' . $option->name;
                                }
                            }
                            $variationNames = implode(' > ', $names);
                        }
                    } else {
                        $targetModel = $product;
                        $targetClass = Product::class;
                        $price       = $product->buying_price;
                        $sku         = $product->sku;
                    }

                    $total = $p['quantity'] * $price;

                    Stock::create([
                        'model_type'      => $targetClass,
                        'model_id'        => $targetModel->id,
                        'warehouse_id'    => $warehouseId,
                        'reference'       => 'S' . time(),
                        'item_type'       => $targetClass,
                        'item_id'         => $targetModel->id,
                        'product_id'      => $product->id,
                        'variation_id'    => $variationId,
                        'variation_names' => $variationNames,
                        'price'           => $price,
                        'quantity'        => $p['quantity'],
                        'expiry_date'     => $expiryDate,
                        'discount'        => 0,
                        'tax'             => 0,
                        'batch'           => $batch,
                        'subtotal'        => $total,
                        'total'           => $total,
                        'sku'             => $sku,
                        'status'          => StockStatus::RECEIVED,
                        'creator'         => auth()->id(),
                        'user_id'         => auth()->id(),
                    ]);

                    $this->updatePreOrders($targetModel);
                }

                return response()->json(['message' => 'Stock stored successfully']);
            } catch (Exception $e) {
                Log::error('Store Stock Error: ' . $e->getMessage());
                throw new Exception($e->getMessage(), 422);
            }
        }

        /**
         * @throws Exception
         */
        public function transferStock(StockTransferRequest $request): object
        {
            try {
                DB::transaction(function () use ($request) {
                    $products        = json_decode($request->input('products'), true);
                    $batch           = 'B' . time();
                    $type            = (int) $request->type;
                    $status          = $type === StockType::TRANSFER->value ? StockStatus::IN_TRANSIT : StockStatus::PENDING;
                    $driver          = $request->driver;
                    $numberPlate     = $request->number_plate;
                    $referencePrefix = $type === StockType::TRANSFER->value ? 'ST' : 'SR';
                    $reference       = $referencePrefix . time();

                    foreach ($products as $p) {
                        $product = Product::findOrFail($p['product_id']);
                        $total   = $product->buying_price * $p['quantity'];

                        $this->stock = Stock::create([
                            'model_type'               => Purchase::class,
                            'model_id'                 => $product->id,
                            'batch'                    => $batch,
                            'type'                     => $type,
                            'reference'                => $reference,
                            'quantity'                 => -$p['quantity'],
                            'request_quantity'         => $p['quantity'],
                            'source_warehouse_id'      => $request->source_warehouse_id,
                            'destination_warehouse_id' => $request->destination_warehouse_id,
                            'item_type'                => Product::class,
                            'product_id'               => $product->id,
                            'item_id'                  => $product->id,
                            'price'                    => $total,
                            'discount'                 => 0,
                            'tax'                      => 0,
                            'subtotal'                 => $total,
                            'total'                    => $total,
                            'sku'                      => $product->sku,
                            'status'                   => $status->value,
                        ]);

                        if ($driver && $numberPlate) {
                            // Include directly in create to save an extra UPDATE query
                            $this->stock->update(['driver' => $driver, 'number_plate' => $numberPlate]);
                        }
                    }
                });

                return $this->stock;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * @throws Exception
         */
        public function reconcileStock(StockReconcilliationRequest $request): object
        {
            try {
                DB::transaction(function () use ($request) {
                    if (!$request->products) return;

                    $products = json_decode($request->products, true);
                    $batch    = 'B' . time();
                    $type     = $request->type;

                    foreach ($products as $product) {
                        $actionType  = (int) $product['action_type'];
                        $baseProduct = Product::findOrFail($product['product_id']);
                        $stock       = $baseProduct->stock;
                        $difference  = $product['physical_count'] - $stock;
                        $quantity    = $product['physical_count'];
                        $total       = $baseProduct->buying_price * $difference;

                        $referencePrefix = match (StockType::from($type)) {
                            StockType::TRANSFER       => 'ST',
                            StockType::RECONCILIATION => 'RST',
                            default                   => 'SR',
                        };

                        $this->stock = Stock::create([
                            'model_type'      => Purchase::class,
                            'model_id'        => 1,
                            'creator'         => auth()->id(),
                            'batch'           => $batch,
                            'type'            => $type,
                            'reference'       => $referencePrefix . time(),
                            'warehouse_id'    => $request->warehouse_id,
                            'description'     => $product['notes'],
                            'item_type'       => Product::class,
                            'product_id'      => $product['product_id'],
                            'item_id'         => $product['product_id'],
                            'system_stock'    => $stock,
                            'physical_stock'  => $product['physical_count'],
                            'difference'      => $difference,
                            'unit_id'         => 1,
                            'discrepancy'     => null,
                            'classification'  => null,
                            'variation_names' => null,
                            'price'           => $baseProduct->buying_price * $difference,
                            'quantity'        => 0,
                            'discount'        => 0,
                            'tax'             => 0,
                            'subtotal'        => $total,
                            'total'           => $total,
                            'sku'             => $baseProduct->sku,
                            'status'          => StockStatus::RECEIVED,
                        ]);

                        match ($actionType) {
                            StockReconciliationType::SELLABLE->value => $this->stock->update(['quantity' => $difference]),
                            StockReconciliationType::RESERVED->value => $this->stock->increment('quantity_ordered', $quantity - $baseProduct->deposited),
                            default => (function () use ($quantity) {
                                $this->stock->increment('quantity', $quantity);
                                $this->stock->decrement('quantity_ordered', $quantity);
                            })(),
                        };

                        activityLog("Added stock reconciliation for: $baseProduct->name");
                    }
                });

                return response()->json([]);
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Purchase request ────────────────────────────────────────────────────────

        public function request(StockPurchaseRequestRequest $request): array
        {
            try {
                DB::transaction(function () use ($request) {
                    $warehouseId      = Warehouse::value('id');
                    $purchaseRequest  = StockPurchaseRequest::create([
                        'reason'         => $request->reason,
                        'reference'      => 'PR' . time(),
                        'requester_name' => $request->requester_name,
                        'date'           => now(),
                        'department'     => $request->department,
                        'priority'       => $request->priority,
                        'supplier_id'    => $request->supplier_id,
                    ]);

                    activityLog('Added stock Request: ' . $purchaseRequest->id);

                    if ($request->items) {
                        $products   = json_decode($request->items, true);
                        $stockRows  = array_map(fn($product) => [
                            'model_type'       => StockPurchaseRequest::class,
                            'reference'        => 'S' . time(),
                            'model_id'         => $purchaseRequest->id,
                            'expiry_date'      => $product['expiry'] ?? null,
                            'item_type'        => Product::class,
                            'product_id'       => $product['product_id'],
                            'item_id'          => $product['product_id'],
                            'variation_names'  => null,
                            'price'            => $product['price'],
                            'quantity'         => 0,
                            'quantity_ordered' => $product['quantity'],
                            'discount'         => 0,
                            'tax'              => 0,
                            'subtotal'         => $product['price'],
                            'total'            => $product['price'],
                            'sku'              => null,
                            'warehouse_id'     => $warehouseId,
                            'status'           => StockStatus::IN_TRANSIT,
                        ], $products);

                        // Bulk insert instead of N individual queries
                        Stock::insert($stockRows);
                    }
                });

                return [];
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── CRUD helpers ────────────────────────────────────────────────────────────

        /**
         * @throws Exception
         */
        public function show(Purchase $purchase): Purchase
        {
            try {
                return $purchase->load('media');
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function showIngredient(Purchase $purchase): Purchase
        {
            try {
                $found = Purchase::where(['id' => $purchase->id, 'type' => PurchaseType::STOCK_PURCHASE])->firstOrFail();
                return $found->load('media');
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * @throws Exception
         */
        public function edit(Purchase $purchase): Purchase
        {
            return $purchase; // No transformation needed; resource handles it
        }

        /**
         * @throws Exception
         */
        public function update(PurchaseRequest $request, Purchase $purchase): object
        {
            try {
                DB::transaction(function () use ($request, $purchase) {
                    $purchase->update([
                        'supplier_id'  => $request->supplier_id,
                        'date'         => Carbon::parse($request->date)->format('Y-m-d H:i:s'),
                        'reference_no' => $request->reference_no,
                        'subtotal'     => $request->subtotal,
                        'tax'          => $request->tax,
                        'discount'     => $request->discount,
                        'total'        => $request->total,
                        'note'         => $request->note ?? '',
                        'status'       => $request->status,
                    ]);

                    if ($request->products) {
                        $products = json_decode($request->products, true);

                        // Delete existing stock & taxes
                        $stockIds = $purchase->stocks->pluck('id');
                        if ($stockIds->isNotEmpty()) {
                            StockTax::whereIn('stock_id', $stockIds)->delete();
                        }
                        $purchase->stocks()->delete();

                        $taxes = Tax::all()->keyBy('id');

                        foreach ($products as $product) {
                            $stock = Stock::create([
                                'model_type'      => Purchase::class,
                                'model_id'        => $purchase->id,
                                'item_type'       => $product['is_variation'] ? ProductVariation::class : Product::class,
                                'product_id'      => $product['product_id'],
                                'variation_names' => $product['variation_names'],
                                'price'           => $product['price'],
                                'quantity'        => $product['quantity'],
                                'discount'        => $product['total_discount'],
                                'tax'             => $product['total_tax'],
                                'subtotal'        => $product['subtotal'],
                                'total'           => $product['total'],
                                'sku'             => $product['sku'],
                                'status'          => $request->status == PurchaseStatus::RECEIVED
                                    ? Status::ACTIVE
                                    : Status::INACTIVE,
                            ]);

                            if (!empty($product['tax_id'])) {
                                $taxRows = [];
                                foreach ($product['tax_id'] as $taxId) {
                                    if (!isset($taxes[$taxId])) continue;
                                    $tax      = $taxes[$taxId];
                                    $taxRows[] = [
                                        'stock_id'   => $stock->id,
                                        'product_id' => $product['product_id'],
                                        'tax_id'     => $tax->id,
                                        'name'       => $tax->name,
                                        'code'       => $tax->code,
                                        'tax_rate'   => $tax->tax_rate,
                                        'tax_amount' => ($tax->tax_rate * ($product['price'] * $product['quantity'])) / 100,
                                    ];
                                }
                                if (!empty($taxRows)) {
                                    StockTax::insert($taxRows); // bulk insert
                                }
                            }
                        }
                    }

                    if ($request->hasFile('file')) {
                        $purchase->getFirstMedia('purchase')?->delete();
                        $purchase->addMediaFromRequest('file')->toMediaCollection('purchase');
                    }
                });

                return $purchase;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(Purchase $purchase): void
        {
            try {
                DB::transaction(function () use ($purchase) {
                    $stockIds = $purchase->stocks->pluck('id');
                    if ($stockIds->isNotEmpty()) {
                        StockTax::whereIn('stock_id', $stockIds)->delete();
                    }
                    $purchase->stocks()->delete();
                    $purchase->getFirstMedia('purchase')?->delete();
                    $purchase->delete();
                });
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception(QueryExceptionLibrary::message($exception), 422);
            }
        }

        public function downloadAttachment(Purchase $purchase)
        {
            return $purchase->getMedia('purchase')->first();
        }

        // ─── Payment methods ─────────────────────────────────────────────────────────

        /**
         * @throws Exception
         */
        public function payment(PurchasePaymentRequest $request, Purchase $purchase): object
        {
            try {
                DB::transaction(function () use ($request, $purchase) {
                    $purchasePayment = PurchasePayment::create([
                        'purchase_id'    => $purchase->id,
                        'date'           => $request->date,
                        'reference_no'   => $request->reference_no,
                        'amount'         => $request->amount,
                        'payment_method' => $request->payment_method,
                        'register_id'    => register()->id,
                    ]);

                    activityLog('Added Stock Purchase Payment: ' . $purchasePayment->id);

                    $expenseCategory = ExpenseCategory::firstOrCreate(
                        ['name' => 'Expense Category'],
                        ['description' => 'description', 'status' => Status::ACTIVE]
                    );

                    Expense::create([
                        'name'                => 'Stock Purchase payment',
                        'amount'              => $request->amount,
                        'date'                => $request->date,
                        'expense_category_id' => $expenseCategory->id,
                        'reference_no'        => $request->reference_no,
                        'is_recurring'        => 0,
                        'expense_type'        => ExpenseType::SYSTEM_CAPTURED->value,
                        'recurs'              => 0,
                        'repetitions'         => 0,
                        'repeats_on'          => null,
                        'paid'                => $request->amount,
                        'paid_on'             => null,
                        'register_id'         => register()->id,
                    ]);

                    if ($request->hasFile('file')) {
                        $purchasePayment->addMediaFromRequest('file')->toMediaCollection('purchase_payment');
                    }

                    $purchase->payment_status = match (true) {
                        $purchase->paid >= $purchase->total => PurchasePaymentStatus::FULLY_PAID,
                        $purchase->paid > 0                 => PurchasePaymentStatus::PARTIAL_PAID,
                        default                             => PurchasePaymentStatus::PENDING,
                    };
                    $purchase->save();
                });

                return $purchase;
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                DB::rollBack();
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function paymentHistory(int $type, Purchase $purchase): object
        {
            try {
                return PurchasePayment::where(['purchase_id' => $purchase->id, 'purchase_type' => $type])->get();
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function paymentDownloadAttachment(PurchasePayment $purchasePayment)
        {
            return $purchasePayment->getMedia('purchase_payment')->first();
        }

        /**
         * @throws Exception
         */
        public function paymentDestroy(Purchase $purchase, PurchasePayment $purchasePayment, int $type): void
        {
            try {
                PurchasePayment::where(['purchase_id' => $purchasePayment->id, 'purchase_type' => $type])->delete();
            } catch (Exception $exception) {
                Log::error($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Private helpers ─────────────────────────────────────────────────────────

        /**
         * Upsert retail prices for a product based on purchase payload.
         */
        private function upsertRetailPrices(Product $productModel, array $product): void
        {
            if (empty($product['retailPrices'])) return;

            $retailData = [];
            foreach ($product['retailPrices'] as $retailPrice) {
                $retailData[] = [
                    'id'            => $retailPrice['id'] ?? null,
                    'item_id'       => $productModel->id,
                    'item_type'     => get_class($productModel),
                    'unit_id'       => $retailPrice['unit_id'],
                    'buying_price'  => $product['price'],
                    'selling_price' => $retailPrice['new_price'],
                ];
            }

            $productModel->retailPrices()->upsert($retailData, ['id'], ['buying_price', 'selling_price', 'unit_id']);
        }

        /**
         * Upsert wholesale prices for a product based on purchase payload.
         */
        private function upsertWholesalePrices(Product $productModel, array $product): void
        {
            if (empty($product['wholesalePrices'])) return;

            $wholesaleData = [];
            foreach ($product['wholesalePrices'] as $wholesalePrice) {
                $wholesaleData[] = [
                    'id'          => $wholesalePrice['id'] ?? null,
                    'item_id'     => $productModel->id,
                    'item_type'   => get_class($productModel),
                    'minQuantity' => $wholesalePrice['min_quantity'],
                    'price'       => $wholesalePrice['new_price'],
                ];
            }

            $productModel->wholesalePrices()->upsert($wholesaleData, ['id'], ['minQuantity', 'price']);
        }

        /**
         * Check pending pre-orders for the given product/variation and mark them
         * READY_FOR_PICKUP when all items have sufficient stock.
         *
         * @param  Product|ProductVariation $targetModel
         */
        private function updatePreOrders($targetModel): void
        {
            $preOrders = Order::with('orderProducts.item')
                              ->where('pre_order_status', PreOrderStatus::PENDING_STOCK)
                              ->whereHas('orderProducts', fn($q) => $q->where('item_id', $targetModel->id))
                              ->get();

            foreach ($preOrders as $preOrder) {
                $allInStock = true;
                foreach ($preOrder->orderProducts as $orderProduct) {
                    $orderProduct->item->refresh();
                    if ($orderProduct->item->stock < $orderProduct->quantity) {
                        $allInStock = false;
                        break;
                    }
                }

                if ($allInStock) {
                    $preOrder->update(['pre_order_status' => PreOrderStatus::READY_FOR_PICKUP]);
                }
            }
        }
    }