<?php

namespace App\Http\Controllers\Admin;

use App\Exports\IngredientExport;
use App\Exports\ItemExport;
use App\Http\Requests\ChangeImageRequest;
use App\Http\Requests\IngredientRequest;
use App\Http\Requests\ItemRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\IngredientResource;
use App\Http\Resources\ItemResource;
use App\Models\Ingredient;
use App\Models\Item;
use App\Services\IngredientsService;
use App\Traits\ApiRequestTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class IngredientsController extends AdminController
{
    use ApiRequestTrait;
    public IngredientsService $ingredientsService;

    public function __construct(IngredientsService $ingredientsService)
    {
        parent::__construct();
        $this->ingredientsService = $ingredientsService;
        $this->middleware(['permission:ingredients'])->only('export', 'changeImage');
        $this->middleware(['permission:ingredients_create'])->only('store');
        $this->middleware(['permission:ingredients_edit'])->only('update');
        $this->middleware(['permission:ingredients_delete'])->only('destroy');
        $this->middleware(['permission:ingredients_show'])->only('show');
    }

    public function index(PaginateRequest $request): Response|AnonymousResourceCollection|Application|ResponseFactory
    {
        try {
            return IngredientResource::collection($this->ingredientsService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Ingredient $ingredient)
    {
        try {
            return new IngredientResource($this->ingredientsService->show($ingredient));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(IngredientRequest $request)
    {
        try {
//            if (config('system.demo')) {
//                return new IngredientResource($this->ingredientsService->store($request));
//            } else {
//                if ($this->apiRequest->status) {
//                    return new IngredientResource($this->ingredientsService->store($request));
//                }
//                return response(['status' => false, 'message' => $this->apiRequest->message], 422);
//            }
            return new IngredientResource($this->ingredientsService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        try {
            return new IngredientResource($this->ingredientsService->update($request, $ingredient));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Ingredient $ingredient): Response|Application|ResponseFactory
    {
        try {
            $this->ingredientsService->destroy($ingredient);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|Application|ResponseFactory
    {
        try {
            return Excel::download(new IngredientExport($this->ingredientsService, $request), 'Ingredient.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
