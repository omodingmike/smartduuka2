<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ItemIngredientRequest;
use App\Http\Resources\ItemIngredientResource;
use App\Models\Ingredient;
use App\Models\ItemIngredient;
use App\Services\ItemIngredientService;
use Exception;
use App\Models\Item;
use App\Models\ItemAddon;
use App\Services\ItemAddonService;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ItemAddonRequest;
use App\Http\Resources\ItemAddonResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemAddonController extends AdminController
{
    public ItemAddonService $itemAddonService;
    public ItemIngredientService $itemIngredientService;

    public function __construct(ItemAddonService $itemAddonService)
    {
        parent::__construct();
        $this->itemAddonService = $itemAddonService;
        $this->middleware(['permission:items_show'])->only('index', 'store', 'destroy');
    }

    public function index(PaginateRequest $request, Item $item) : Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return ItemAddonResource::collection($this->itemAddonService->list($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function ingredients(PaginateRequest $request, Item $item)
    {
        try {
            return ItemIngredientResource::collection($item->ingredients) ;
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ItemAddonRequest $request, Item $item) : Response | ItemAddonResource | Application | ResponseFactory
    {
        try {
            return new ItemAddonResource($this->itemAddonService->store($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function storeIngredients(ItemIngredientRequest $request, Item $item)
    {
        try {
           $this->itemAddonService->storeIngredient($request, $item);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Item $item, ItemAddon $itemAddon) : Response | Application | ResponseFactory
    {
        try {
            $this->itemAddonService->destroy($item, $itemAddon);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function destroyIngredient(Item $item, ItemIngredient $itemIngredient) : Response | Application | ResponseFactory
    {
        try {
            $item->ingredients()->detach($itemIngredient->id);
            return response('', 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
