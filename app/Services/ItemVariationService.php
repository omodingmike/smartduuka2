<?php

namespace App\Services;


use App\Http\Requests\ItemVariationRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Item;
use App\Models\ItemVariation;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemVariationService
{
    public $itemVariation;
    protected $itemVariationFilter = [
        'product_id',
        'item_attribute_id',
        'price',
        'status'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request, Item $item)
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            return ItemVariation::with(['item', 'itemAttribute'])->where(['product_id' => $item->id])->where(
                function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->itemVariationFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            )->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function listGroupByAttribute(PaginateRequest $request, Item $item): Collection
    {
        try {
            $requests = $request->all();
            $method = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType = $request->get('order_type') ?? 'desc';

            $itemVariations = ItemVariation::with(['item', 'itemAttribute', 'ingredients'])->where(['product_id' => $item->id])->where(
                function ($query) use ($requests) {
                    foreach ($requests as $key => $request) {
                        if (in_array($key, $this->itemVariationFilter)) {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            )->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );

            $array = [];
            if ($itemVariations) {
                foreach ($itemVariations as $itemVariation) {
                    if (!isset($array[$itemVariation->item_attribute_id])) {
                        $array[$itemVariation->item_attribute_id] = (object)[
                            'item_attribute_id' => $itemVariation->item_attribute_id,
                            'item_attribute'    => $itemVariation->itemAttribute,
                            'children'          => [
                                (object)[
                                    'id'                => $itemVariation->id,
                                    'product_id'           => $itemVariation->product_id,
                                    'item_attribute_id' => $itemVariation->item_attribute_id,
                                    'name'              => $itemVariation->name,
                                    'price'             => $itemVariation->price,
                                    'caution'           => $itemVariation->caution,
                                    'status'            => $itemVariation->status,
                                    'item'              => $itemVariation->item,
                                    'itemAttribute'     => $itemVariation->itemAttribute,
                                    'ingredients'       => $itemVariation->ingredients,
                                    'overall_cost'      => $itemVariation->overall_cost
                                ]
                            ]
                        ];
                    } else {
                        $array[$itemVariation->item_attribute_id]->children[] = (object)[
                            'id'                => $itemVariation->id,
                            'product_id'           => $itemVariation->product_id,
                            'item_attribute_id' => $itemVariation->item_attribute_id,
                            'name'              => $itemVariation->name,
                            'price'             => $itemVariation->price,
                            'caution'           => $itemVariation->caution,
                            'status'            => $itemVariation->status,
                            'item'              => $itemVariation->item,
                            'itemAttribute'     => $itemVariation->itemAttribute,
                            'ingredients'       => $itemVariation->ingredients,
                            'overall_cost'      => $itemVariation->overall_cost
                        ];
                    }
                }
            }
            return collect($array);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemVariationRequest $request, Item $item)
    {
        try {
            $variationItem = ItemVariation::create($request->validated() + ['product_id' => $item->id]);
            activityLog('Created ItemVariation: ' . $variationItem->name);
            $syncData = [];
            if ($request->ingredients) {
                foreach (json_decode($request->ingredients, true) as $ingredient) {
                    $syncData[$ingredient['ingredient_id']] = [
                        'quantity'     => $ingredient['quantity'],
                        'buying_price' => $ingredient['buying_price'],
                        'total'        => $ingredient['total'],
                    ];
                }
                $variationItem->ingredients()->syncWithoutDetaching($syncData);
            }
            return $variationItem;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemVariationRequest $request, Item $item, ItemVariation $itemVariation): ItemVariation
    {
        try {
            if ($item->id == $itemVariation->product_id) {
                if ($request->ingredients) {
                    $syncData = [];
                    foreach (json_decode($request->ingredients, true) as $ingredient) {
                        $syncData[$ingredient['ingredient_id']] = [
                            'quantity'     => $ingredient['quantity'],
                            'buying_price' => $ingredient['buying_price'],
                            'total'        => $ingredient['total'],
                        ];
                    }
                    $itemVariation->ingredients()->sync($syncData);
                }
                activityLog("Updated ItemVariation: " . $itemVariation->name);
                return tap($itemVariation)->update($request->validated());
            }
            return $itemVariation;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function destroy(Item $item, ItemVariation $itemVariation): void
    {
        try {
            if ($item->id == $itemVariation->product_id) {
                $itemVariation->delete();
                activityLog("Deleted ItemVariation: " . $itemVariation->name);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            DB::rollBack();
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * @throws Exception
     */
    public function show(Item $item, ItemVariation $itemVariation)
    {
        try {
            return ItemVariation::where(['product_id' => $item->id, 'id' => $itemVariation->id])->first();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    private function recursiveDelete($variation)
    {
        $itemId = $variation->product_id;
        $variation->delete();

        $Variations = ItemVariation::where(['product_id' => $itemId])->get();
        foreach ($Variations as $Variation) {
            $this->recursiveDelete($Variation);
        }
    }
}
