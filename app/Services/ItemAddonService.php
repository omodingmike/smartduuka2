<?php

namespace App\Services;


use App\Http\Requests\ItemAddonRequest;
use App\Http\Requests\ItemIngredientRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Item;
use App\Models\ItemAddon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemAddonService
{
    public $itemExtra;
    protected $itemExtraFilter = [
        'product_id',
        'name',
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

            return ItemAddon::with('item', 'addonItem')->where(['product_id' => $item->id])->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->itemExtraFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(ItemAddonRequest $request, Item $item)
    {
        try {
            $item =  ItemAddon::create($request->validated() + ['product_id' => $item->id]);
            activityLog('Created Item Extra: ' . $item->name);
            return $item;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function storeIngredient(ItemIngredientRequest $request, Item $item)
    {
        try {
            DB::transaction(function () use ($request, $item) {
                $syncData = [];
                foreach (json_decode($request->ingredients, true) as $ingredient) {
                    $syncData[$ingredient['ingredient_id']] = [
                        'quantity'     => $ingredient['quantity'],
                        'buying_price' => $ingredient['buying_price'],
                        'total'        => $ingredient['total'],
                    ];
                }
                $item->ingredients()->syncWithoutDetaching($syncData);
                activityLog("Added ingredients to item: $item->name");
                return $item->ingredients->last();
            });
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item, ItemAddon $itemExtra)
    {
        try {
            if ($item->id == $itemExtra->product_id) {
                $itemExtra->delete();
                activityLog('Deleted Item Extra: ' . $itemExtra->name);
            } else {
                throw new Exception(trans('all.item_match'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
