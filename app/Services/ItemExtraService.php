<?php

namespace App\Services;


use Exception;
use App\Models\Item;
use App\Models\ItemExtra;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ItemExtraRequest;

class ItemExtraService
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
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return ItemExtra::with('item')->where(['product_id' => $item->id])->where(function ($query) use ($requests) {
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
    public function store(ItemExtraRequest $request, Item $item)
    {
        try {
            $extra = ItemExtra::create($request->validated() + ['product_id' => $item->id]);
            activityLog('Created Item Extra: ' . $extra->name);
            return $extra;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(ItemExtraRequest $request, Item $item, ItemExtra $itemExtra)
    {
        try {
            if ($item->id == $itemExtra->product_id) {
                $item =  tap($itemExtra)->update($request->validated());
                activityLog('Updated Item Extra: ' . $item->name);
                return $item;
            } else {
                throw new Exception(trans('all.item_match'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Item $item, ItemExtra $itemExtra)
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

    /**
     * @throws Exception
     */
    public function show(Item $item, ItemExtra $itemExtra)
    {
        try {
            if ($item->id == $itemExtra->product_id) {
                return $itemExtra;
            } else {
                throw new Exception(trans('all.item_match'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
