<?php

    namespace App\Services;

    use App\Enums\Ask;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\ProductCategoryResource;
    use App\Models\Damage;
    use App\Models\Ingredient;
    use App\Models\ProductAttribute;
    use App\Models\ProductAttributeOption;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use Exception;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Http\Request;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class StockService
    {
        // ─── Core query builder ──────────────────────────────────────────────────────

        /**
         * Base Eloquent query shared by list/transfer/batch methods.
         * Excludes ingredient model types and soft-deleted products.
         */
        private function stockQuery(Request $request): Builder
        {
            return Stock::with(['stockProducts.item', 'user'])
                        ->where('model_type', '<>', Ingredient::class)
                        ->when($request->warehouse_id, fn($q) => $q->where('warehouse_id', $request->warehouse_id))
                        ->whereHas('product', fn(Builder $q) => $q->withoutTrashed())
                        ->orderBy('created_at', 'desc');
        }

        // ─── Listing methods ─────────────────────────────────────────────────────────

        /**
         * Paginated stock list grouped by product/warehouse/variation.
         * Passes total stock value and low-stock count back via reference parameters.
         */
        public function list(Request $request, ?float &$totalStockValue = null, ?int &$totalLowStockCount = null): LengthAwarePaginator
        {
            try {
                $perPage      = $request->integer('per_page', 10);
                $warehouseId  = $request->warehouse_id;
                $query        = $request->input('query');
                $page         = $request->input('page');

                $stocks = $this->stockQuery($request)
                               ->where('status', StockStatus::RECEIVED)
                               ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                               ->when($query, fn($q) => $q->whereHas('product', fn($q) => $q->where('name', 'ilike', '%' . $query . '%')))
                               ->get();

                $groupCriteria = enabledWarehouse()
                    ? fn($item) => $item->product_id . '-' . $item->warehouse_id . '-' . $item->variation_id
                    : fn($item) => $item->product_id . '-' . $item->item_type . '-' . $item->variation_names . '-' . $item->variation_id;

                $processedItems = $stocks
                    ->groupBy($groupCriteria)
                    ->map(fn($group) => $this->transformStockGrouped($group))
                    ->filter(fn($item) => $item !== null && ($item['stock'] > 0 || $item['quantity_deposited'] > 0))
                    ->values();

                $totalStockValue    = $processedItems->sum('total_price');
                $totalLowStockCount = $processedItems->filter(
                    fn($item) => $item['stock'] <= $item['low_stock_quantity_warning']
                )->count();

                return $this->paginate($processedItems, $perPage, $page);
            } catch (Exception $exception) {
                Log::error('Stock list error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Paginated stock list grouped by product + batch + warehouse.
         */
        public function listGroupedByBatch(Request $request): LengthAwarePaginator
        {
            try {
                $perPage     = $request->integer('per_page', 10);
                $warehouseId = $request->warehouse_id;
                $query       = $request->input('query');
                $page        = $request->input('page');

                $stocks = $this->stockQuery($request)
                               ->where('status', StockStatus::RECEIVED)
                               ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                               ->when($query, fn($q) => $q->whereHas('product', fn($q) => $q->where('name', 'ilike', '%' . $query . '%')))
                               ->get();

                $processedItems = $stocks
                    ->groupBy(fn($item) => $item->product_id . '-' . $item->batch . '-' . $item->warehouse_id . '-' . $item->variation_id)
                    ->map(fn($group) => $this->transformStockGrouped($group))
                    ->filter(fn($item) => $item !== null && $item['stock'] > 0)
                    ->values();

                return $this->paginate($processedItems, $perPage, $page, url('/api/admin/stock/batch'));
            } catch (Exception $exception) {
                Log::error('Stock batch list error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Paginated list of transfers/reconciliations grouped by batch.
         * Uses DB-level batch pagination to avoid loading all rows into memory.
         */
        public function transfers(Request $request): LengthAwarePaginator
        {
            try {
                $perPage = $request->integer('per_page', 10);
                $type    = $request->type;

                $baseQuery = fn() => $this->stockQuery($request)
                                          ->when($type, fn($q) => $q->where('type', $type));

                // Paginate distinct batches at the DB level
                $batchPage = $baseQuery()
                    ->select('batch', DB::raw('MAX(created_at) as created_at'))
                    ->groupBy('batch')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage );

                $batches = $batchPage->pluck('batch')->filter()->values();

                if ($batches->isEmpty()) {
                    $batchPage->setCollection(collect());
                    return $batchPage;
                }

                // Fetch all rows for those batches in a single query
                $stocks = $baseQuery()
                    ->whereIn('batch', $batches)
                    ->get()
                    ->groupBy('batch');


                $processedItems = $batches->map(function ($batch) use ($stocks) {
                    $group = $stocks->get($batch);
                    return ($group && $group->isNotEmpty()) ? $this->groupedStock($group, $batch) : null;
                })->filter()->values();


                $batchPage->setCollection($processedItems);

                return $batchPage;
            } catch (Exception $exception) {
                Log::error('Transfer list error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Ingredient stock grouped by item_id and summed.
         */
        public function listIngredients(PaginateRequest $request): array
        {
            try {
                $requests    = $request->all();
                $orderColumn = $request->get('order_column', 'id');
                $orderType   = $request->get('order_type', 'desc');

                $stockFilter = ['name', 'status'];

                $query = Stock::with('item')
                              ->where('status', Status::ACTIVE)
                              ->where('item_type', Ingredient::class);

                foreach ($requests as $key => $value) {
                    if (!in_array($key, $stockFilter)) {
                        continue;
                    }
                    if ($key === 'product_name') {
                        $query->whereHas('item', fn($q) => $q->where('name', 'like', '%' . $value . '%'));
                    } else {
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }

                $stocks = $query->orderBy($orderColumn, $orderType)->get();

                if ($stocks->isEmpty()) {
                    return [];
                }

                return $stocks->groupBy('item_id')->map(function ($group) {
                    $first = $group->first();
                    return [
                        'quantity'       => $group->sum('quantity'),
                        'quantity_alert' => $first->item->quantity_alert ?? null,
                        'name'           => $first->item->name ?? null,
                        'unit'           => $first->item->unit ?? null,
                        'status'         => $first->item->status ?? null,
                    ];
                })->values()->all();
            } catch (Exception $exception) {
                Log::error('List ingredients error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Expiry stock list filtered by status window and paginated.
         */
        public function expiryList(PaginateRequest $request): LengthAwarePaginator
        {
            try {
                $requests    = $request->all();
                $perPage     = $request->get('per_page', 10);
                $page        = $request->get('page', 1);
                $orderColumn = $request->get('order_column', 'id');
                $orderType   = $request->get('order_type', 'desc');

                $stocks = Stock::with([
                    'product.sellingUnits:id,short_name',
                    'product.unit:id,short_name',
                    'warehouse:name,id',
                ])
                               ->when(isset($requests['warehouse_id']), fn($q) => $q->where('warehouse_id', $requests['warehouse_id']))
                               ->when(isset($requests['stock_status']), function ($q) use ($requests) {
                                   match ((int) $requests['stock_status']) {
                                       1 => $q->where('expiry_date', '>', now()->addDays(30)),
                                       2 => $q->whereBetween('expiry_date', [now()->endOfDay(), now()->addDays(30)]),
                                       default => $q->where('expiry_date', '<=', now()->endOfDay()),
                                   };
                               })
                               ->where('status', StockStatus::RECEIVED)
                               ->whereNotNull('expiry_date')
                               ->where('model_type', '<>', Ingredient::class)
                               ->orderBy($orderColumn, $orderType)
                               ->get();

                return $this->paginate($stocks, $perPage, $page);
            } catch (Exception $exception) {
                Log::error('Expiry list error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Stock transfer detail (single batch).
         */
        public function transfer(Request $request): Collection
        {
            try {
                return Stock::with(['product.sellingUnits:id,code', 'product.unit:id,code'])
                            ->where('reference', 'like', 'ST%')
                            ->where('batch', $request->batch)
                            ->where('model_type', '<>', Ingredient::class)
                            ->get()
                            ->unique('product_id')
                            ->values();
            } catch (Exception $exception) {
                Log::error('Transfer detail error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Wastage & stock-capture ─────────────────────────────────────────────────

        /**
         * Merged wastage list (damages + expired stock) with total loss calculation.
         */
        public function wastage(Request $request, float &$totalLoss = 0): LengthAwarePaginator
        {
            try {
                $perPage     = $request->integer('per_page', 10);
                $page        = $request->integer('page', 1);
                $warehouseId = $request->warehouse_id;

                $damages = Stock::with(['item', 'model'])
                                ->where('model_type', Damage::class)
                                ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                                ->get()
                                ->map(fn($stock) => $this->mapWastageRow($stock, 'Damage', fn($s) => $s->model->reason ?? 'N/A', fn($s) => abs($s->quantity)));

                $expired = Stock::with(['item'])
                                ->where('expiry_date', '<', now())
                                ->where('status', StockStatus::RECEIVED)
                                ->where('quantity', '>', 0)
                                ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                                ->get()
                                ->map(fn($stock) => $this->mapWastageRow($stock, 'Wastage', fn() => 'Expired', fn($s) => $s->quantity, 'expiry_date'));

                $wastage   = $damages->merge($expired)->sortByDesc('date')->values();
                $totalLoss = $wastage->sum(fn($item) => $item->qtyOut * $item->unitCost);

                return $this->paginate($wastage, $perPage, $page, url('/api/admin/stock/wastage'));
            } catch (Exception $exception) {
                Log::error('Wastage error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        /**
         * Paginated stock-take/capture records grouped by batch.
         */
        public function stockCapture(Request $request): LengthAwarePaginator
        {
            try {
                $perPage     = $request->integer('per_page', 10);
                $page        = $request->integer('page', 1);
                $warehouseId = $request->warehouse_id;

                $stockTakes = Stock::with(['warehouse', 'user', 'item'])
                                   ->where('quantity', '>', 0)
                                   ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
                                   ->get()
                                   ->groupBy('batch');

                $formattedTakes = $stockTakes->map(function ($stocks, $batch) {
                    if (!$stocks) return null;
                    $first = $stocks->first();

                    return (object) [
                        'id'           => $batch,
                        'date'         => $first->created_at,
                        'branch'       => $first->warehouse->name ?? 'N/A',
                        'capturedBy'   => $first->user->name ?? 'N/A',
                        'itemsCounted' => $stocks->count(),
                        'status'       => $first->status,
                        'items'        => $stocks->map(function ($stock) {
                            $item = $stock->item;
                            $name = $item->name ?? 'Unknown';

                            if ($item instanceof ProductVariation) {
                                $item->loadMissing('productAttributeOption.productAttribute');
                                if ($item->productAttributeOption) {
                                    $attr = $item->productAttributeOption->productAttribute;
                                    $name = $item->product->name . ' - ' . $attr->name . ' (' . $item->productAttributeOption->name . ')';
                                }
                            }

                            return [
                                'itemName' => $name,
                                'expected' => $stock->system_stock,
                                'counted'  => $stock->physical_stock,
                                'variance' => $stock->difference,
                                'itemCode' => $item->sku ?? 'N/A',
                                'unitCost' => $item->buying_price ?? 0,
                            ];
                        }),
                    ];
                })->filter()->sortByDesc('date')->values();

                return $this->paginate($formattedTakes, $perPage, $page, url('/api/admin/inventory-report/stock-capture'));
            } catch (Exception $exception) {
                Log::error('Stock capture error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        // ─── Transform helpers ───────────────────────────────────────────────────────

        /**
         * Map a stock row into a normalised wastage object.
         *
         * @param  Stock    $stock
         * @param  string   $type       'Damage' | 'Wastage'
         * @param  callable $reasonFn   Receives $stock, returns reason string
         * @param  callable $qtyFn      Receives $stock, returns quantity
         * @param  string   $dateProp   Which date column to use
         */
        private function mapWastageRow(
            Stock $stock,
            string $type,
            callable $reasonFn,
            callable $qtyFn,
            string $dateProp = 'created_at'
        ): object {
            $item        = $stock->item;
            $name        = $item->name ?? 'Unknown Item';
            $buyingPrice = $item->buying_price ?? 0;

            if ($item instanceof ProductVariation) {
                $item->loadMissing('productAttributeOption.productAttribute');
                if ($item->productAttributeOption) {
                    $attr        = $item->productAttributeOption->productAttribute;
                    $name        = $item->product->name . ' - ' . $attr->name . ' (' . $item->productAttributeOption->name . ')';
                }
                $buyingPrice = $item->buying_price ?? $item->product->buying_price ?? 0;
            }

            return (object) [
                'id'       => $stock->id,
                'date'     => $stock->{$dateProp},
                'itemCode' => $item->sku ?? 'N/A',
                'itemName' => $name,
                'type'     => $type,
                'reason'   => $reasonFn($stock),
                'qtyOut'   => $qtyFn($stock),
                'unitCost' => $buyingPrice,
                'branch'   => $stock->warehouse->name ?? 'Main Branch',
            ];
        }

        /**
         * Resolve a human-readable variation name for a stock item, falling back
         * to the stored variation_names string.
         */
        private function resolveVariationName(Stock $first): string
        {
            if (!$first->variation_id) {
                return $first->variation_names ?? '';
            }

            $variation = ProductVariation::with('productAttributeOption.productAttribute')->find($first->variation_id);
            if ($variation && $variation->productAttributeOption) {
                $attr = $variation->productAttributeOption->productAttribute;
                return $attr->name . '(' . $variation->productAttributeOption->name . ')';
            }

            return $first->variation_names ?? '';
        }

        /**
         * Transform a collection of stock rows (same product/warehouse group) into
         * a summary array for the stock list.
         */
        protected function transformStockGrouped(Collection $group): ?array
        {
            if (!$group || $group->isEmpty()) return null;

            $first         = $group->first();
            $isPurchasable = $first->product->can_purchasable !== Ask::NO;
            $status        = $first->status;
            $variationNames = $this->resolveVariationName($first);

            $quantity           = $isPurchasable ? $group->sum('quantity') : 0;
            $quantityReceived   = $isPurchasable ? $group->sum('quantity_received') : 0;
            $quantityDeposited  = $isPurchasable ? $group->sum('quantity_ordered') : 0;
            $netDeposited       = $quantityDeposited - $quantityReceived;
            $unitPrice          = $first->product->buying_price;

            // Avoid N+1: only look up attribute/option when IDs are present
            $attribute       = $first->product_attribute_id
                ? ProductAttribute::find($first->product_attribute_id)
                : null;
            $attributeOption = $first->product_attribute_option_id
                ? ProductAttributeOption::find($first->product_attribute_option_id)
                : null;

            return [
                'product_id'                  => $first->product_id,
                'products'                    => $first->products,
                'stock_status'                => ['value' => $status->value, 'label' => $status->label()],
                'product_name'                => $first->product->name,
                'category'                    => new ProductCategoryResource($first->product->category),
                'unit'                        => $first->product->unit,
                'other_unit'                  => $first->product->otherUnit,
                'units_nature'                => $first->product->units_nature,
                'variation_names'             => $variationNames,
                'variation_id'                => $first->variation_id,
                'status'                      => $first->product->status,
                'warehouse_id'                => $first->warehouse_id,
                'reference'                   => $first->reference,
                'quantity_deposited'          => $netDeposited,
                'delivery'                    => $first->delivery,
                'system_stock'                => $first->system_stock,
                'physical_stock'              => $first->physical_stock,
                'difference'                  => $first->difference,
                'discrepancy'                 => $first->discrepancy,
                'classification'              => $first->classification,
                'creator'                     => $first->user,
                'batch'                       => $first->batch,
                'weight'                      => $first->product->weight,
                'source_warehouse_id'         => $first->source_warehouse_id,
                'total'                       => $first->total,
                'destination_warehouse_id'    => $first->destination_warehouse_id,
                'created_at'                  => $first->created_at,
                'description'                 => $first->description,
                'stock'                       => $isPurchasable ? $quantity : 'N/C',
                'quantity_received'           => $isPurchasable ? $quantityReceived : 'N/C',
                'other_stock'                 => $isPurchasable ? $group->sum('other_quantity') : 'N/C',
                'unit_price'                  => $unitPrice,
                'total_price'                 => $quantity * $unitPrice,
                'product_attribute_id'        => $first->product_attribute_id,
                'product_attribute_option_id' => $first->product_attribute_option_id,
                'attribute'                   => $attribute,
                'attribute_option'            => $attributeOption,
                'expiry_date'                 => $first->expiry_date,
                'low_stock_quantity_warning'  => $first->product->low_stock_quantity_warning,
            ];
        }

        /**
         * Transform a batch group into a transfer summary object.
         */
        private function groupedStock(Collection $group, string $batch): object
        {
            $first = $group->first();

            $productsWithQuantity = $group->map(function ($stock) {
                $product = $stock->product;
                if ($product) {
                    $product->transfer_quantity = $stock->quantity;
                    $product->request_quantity  = $stock->request_quantity;
                    $product->approve_quantity  = $stock->approve_quantity;
                }
                return $product;
            })->filter()->values();

            return (object) [
                'id'                       => $first->id,
                'batch'                    => $batch,
                'reference'                => $first->reference,
                'products'                 => $productsWithQuantity,
                'user'                     => $first->user,
                'warehouse_id'             => $first->warehouse_id,
                'source_warehouse_id'      => $first->source_warehouse_id,
                'destination_warehouse_id' => $first->destination_warehouse_id,
                'price'                    => $group->sum('price'),
                'quantity'                 => $group->sum('quantity'),
                'request_quantity'         => $group->sum('request_quantity'),
                'approve_quantity'         => $group->sum('approve_quantity'),
                'discount'                 => $group->sum('discount'),
                'tax'                      => $group->sum('tax'),
                'subtotal'                 => $group->sum('subtotal'),
                'total'                    => $group->sum('total'),
                'delivery'                 => $group->sum('delivery'),
                'status'                   => $first->status,
                'type'                     => $first->type,
                'distribution_status'      => $first->distribution_status,
                'created_at'               => $first->created_at,
                'updated_at'               => $first->updated_at,
                'description'              => $first->description,
                'expiry_date'              => $first->expiry_date,
                'system_stock'             => $first->system_stock,
                'physical_stock'           => $first->physical_stock,
                'difference'               => $first->difference,
                'discrepancy'              => $first->discrepancy,
                'classification'           => $first->classification,
                'product_id'               => $first->product_id,
                'model_type'               => $first->model_type,
                'model_id'                 => $first->model_id,
                'item_type'                => $first->item_type,
                'item_id'                  => $first->item_id,
                'sku'                      => $first->sku,
                'variation_names'          => $first->variation_names,
                'variation_id'             => $first->variation_id,
                'unit_id'                  => $first->unit_id,
                'rate'                     => $first->rate,
                'purchase_quantity'        => $first->purchase_quantity,
                'fractional_quantity'      => $first->fractional_quantity,
                'other_quantity'           => $first->other_quantity,
                'sold'                     => $group->sum('sold'),
                'returned'                 => $group->sum('returned'),
                'creator'                  => $first->creator,
                'user_id'                  => $first->user_id,
                'driver'                   => $first->driver ?? null,
                'number_plate'             => $first->number_plate ?? null,
            ];
        }

        // ─── Pagination helper ───────────────────────────────────────────────────────

        /**
         * Manually paginate a Collection (used when DB-level pagination isn't possible).
         */
        public function paginate(
            $items,
            int $perPage = 15,
            ?int $page = null,
            ?string $baseUrl = null,
            array $options = []
        ): LengthAwarePaginator {
            $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $items instanceof Collection
                ? $items->filter()
                : Collection::make($items)->filter();

            $paginator = new LengthAwarePaginator(
                $items->values()->forPage($page, $perPage),
                $items->count(),
                $perPage,
                $page,
                $options
            );

            if ($baseUrl) {
                $paginator->setPath($baseUrl);
            }

            return $paginator;
        }
    }