<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ItemExport;
use App\Http\Resources\IngredientResource;
use Exception;
use App\Models\Item;
use App\Services\ItemService;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\ChangeImageRequest;
use App\Traits\ApiRequestTrait;

class ItemController extends AdminController
{
    use ApiRequestTrait;
    protected $apiRequest;
    public ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        parent::__construct();
//        $this->apiRequest = $this->makeApiRequest();
        $this->itemService = $itemService;
        $this->middleware(['permission:items'])->only('export', 'changeImage');
        $this->middleware(['permission:items_create'])->only('store');
        $this->middleware(['permission:items_edit'])->only('update');
        $this->middleware(['permission:items_delete'])->only('destroy');
        $this->middleware(['permission:items_show'])->only('show');
    }

    public function index(PaginateRequest $request): Response|AnonymousResourceCollection|Application|ResponseFactory
    {
        try {
            return ItemResource::collection($this->itemService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function purchasable(PaginateRequest $request): Response|AnonymousResourceCollection|Application|ResponseFactory
    {
        try {
            return ItemResource::collection($this->itemService->purchasableList($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
    public function purchasableIngredients(PaginateRequest $request)
    {
        try {
            return IngredientResource ::collection($this->itemService->purchasableIngredientsList($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception -> getMessage()], 422);
        }
    }


    public function show(Item $item): Response|ItemResource|Application|ResponseFactory
    {
        try {
            return new ItemResource($this->itemService->show($item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ItemRequest $request): Response|ItemResource|Application|ResponseFactory
    {
        try {
            return new ItemResource($this->itemService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ItemRequest $request, Item $item): Response|ItemResource|Application|ResponseFactory
    {
        try {
            return new ItemResource($this->itemService->update($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Item $item): Response|Application|ResponseFactory
    {
        try {
            $this->itemService->destroy($item);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function changeImage(ChangeImageRequest $request, Item $item): Response|ItemResource|Application|ResponseFactory
    {
        try {
            return new ItemResource($this->itemService->changeImage($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|Application|ResponseFactory
    {
        try {
            return Excel::download(new ItemExport($this->itemService, $request), 'Item.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
