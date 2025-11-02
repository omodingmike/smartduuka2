<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\ItemVariationGroupByAttributeResource;
use Exception;
use App\Models\Item;
use App\Http\Requests\PaginateRequest;
use App\Services\ItemVariationService;
use App\Http\Requests\ItemVariationRequest;
use App\Http\Resources\ItemVariationResource;
use App\Models\ItemVariation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ItemVariationController extends AdminController
{
    public ItemVariationService $itemVariationService;

    public function __construct(ItemVariationService $itemVariationService)
    {
        parent::__construct();
        $this->itemVariationService = $itemVariationService;
        $this->middleware(['permission:items_show'])->only('index', 'listGroupByAttribute', 'show', 'store', 'update', 'destroy');
    }

    public function index(PaginateRequest $request, Item $item) : Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return ItemVariationResource::collection($this->itemVariationService->list($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function listGroupByAttribute(PaginateRequest $request, Item $item): Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return ItemVariationGroupByAttributeResource::collection($this->itemVariationService->listGroupByAttribute($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ItemVariationRequest $request, Item $item): Response | ItemVariationResource | Application | ResponseFactory
    {
        try {
            return new ItemVariationResource($this->itemVariationService->store($request, $item));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function update(ItemVariationRequest $request, Item $item, ItemVariation $itemVariation): Response | ItemVariationResource | Application | ResponseFactory
    {
        try {
            return new ItemVariationResource($this->itemVariationService->update($request, $item, $itemVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function show(Item $item, ItemVariation $itemVariation): Response | ItemVariationResource | Application | ResponseFactory
    {
        try {
            return new ItemVariationResource($this->itemVariationService->show($item, $itemVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function destroy(Item $item, ItemVariation $itemVariation): Response | Application | ResponseFactory
    {
        try {
            $this->itemVariationService->destroy($item, $itemVariation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
