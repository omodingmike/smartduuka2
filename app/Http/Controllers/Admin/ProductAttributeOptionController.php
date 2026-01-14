<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductAttributeOptionRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ProductAttributeOptionResource;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use App\Services\ProductAttributeOptionService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductAttributeOptionController extends AdminController
{
    private ProductAttributeOptionService $productAttributeOptionService;

    public function __construct(ProductAttributeOptionService $productAttributeOptionService)
    {
        parent::__construct();
        $this->productAttributeOptionService = $productAttributeOptionService;
        $this->middleware(['permission:settings'])->only('index', 'store', 'update', 'destroy', 'show');
    }

    public function index(PaginateRequest $request, ProductAttribute $productAttribute): Response | AnonymousResourceCollection | Application | ResponseFactory
    {
        try {
            return ProductAttributeOptionResource::collection($this->productAttributeOptionService->list($request, $productAttribute));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ProductAttributeOptionRequest $request, ProductAttribute $productAttribute): Response | ProductAttributeOptionResource | Application | ResponseFactory
    {
        try {
            return new ProductAttributeOptionResource($this->productAttributeOptionService->store($request, $productAttribute));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ProductAttributeOptionRequest $request, ProductAttribute $productAttribute, ProductAttributeOption $productAttributeOption): Response | ProductAttributeOptionResource | Application | ResponseFactory
    {
        try {
            return new ProductAttributeOptionResource($this->productAttributeOptionService->update($request, $productAttribute, $productAttributeOption));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Request $request): Response | Application | ResponseFactory
    {
        try {
            ProductAttributeOption::destroy( $request->ids );
//            $this->productAttributeOptionService->destroy($productAttribute, $productAttributeOption);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(ProductAttribute $productAttribute, ProductAttributeOption $productAttributeOption): Response | ProductAttributeOptionResource | Application | ResponseFactory
    {
        try {
            return new ProductAttributeOptionResource($this->productAttributeOptionService->show($productAttribute, $productAttributeOption));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
