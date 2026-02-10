<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\Product;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\ProductVariation;
use App\Http\Requests\PaginateRequest;
use App\Services\ProductVariationService;
use App\Http\Requests\ProductVariationRequest;
use App\Http\Resources\ProductVariationResource;
use App\Http\Resources\SimpleProductVariationResource;
use App\Http\Resources\SimpleProductVariationResourceAdmin;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductVariationController extends AdminController
{
    private ProductVariationService $productVariationService;
    public function __construct(ProductVariationService $productVariationService)
    {
        parent::__construct();
        $this->productVariationService = $productVariationService;
        $this->middleware(['permission:products_show'])->only('store', 'update', 'destroy', 'show');
    }

    public function tree(Request $request, Product $product): Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->tree($request, $product)]);
    }

    public function singleTree(Product $product): Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->singleTree($product)]);
    }

    public function treeWithSelected(Request $request, Product $product): Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        return response(['data' =>  $this->productVariationService->treeWithSelected($request, $product)]);
    }

    public function index(PaginateRequest $request, Product $product): Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        try {
            return ProductVariationResource::collection($this->productVariationService->list($request, $product));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(ProductVariationRequest $request, Product $product)
    {
        try {
            return $this->productVariationService->store($request, $product);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ProductVariationRequest $request, Product $product, ProductVariation $productVariation): Application| Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return ProductVariationResource::collection($this->productVariationService->update($request, $product, $productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(Product $product, ProductVariation $productVariation): Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        try {
            $this->productVariationService->destroy($product, $productVariation);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(Product $product, ProductVariation $productVariation): Response | ProductVariationResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        try {
            return new ProductVariationResource($this->productVariationService->show($product, $productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function initialVariation(Product $product,Request $request ): Application| Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application|
    ResponseFactory
    {
        try {
            return SimpleProductVariationResource::collection($this->productVariationService->initialVariation($product,$request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function childrenVariation(ProductVariation $productVariation): Application| Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return SimpleProductVariationResource::collection($this->productVariationService->childrenVariation($productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function ancestorsToString(ProductVariation $productVariation): Application| Response|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return response(['data' => $this->productVariationService->ancestorsToString($productVariation)], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function ancestorsAndSelfId(ProductVariation $productVariation): Application| Response|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return response(['data' => $this->productVariationService->ancestorsAndSelfId($productVariation)], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function barcodeVariationProduct(ProductVariation $productVariation): Response | SimpleProductVariationResourceAdmin | \Illuminate\Contracts\Foundation\Application | ResponseFactory
    {
        try {
            return new SimpleProductVariationResourceAdmin($this->productVariationService->barcodeVariationProduct($productVariation));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function downloadBarcode(ProductVariation $productVariation)
    {
        try {
            return $this->productVariationService->downloadBarcode($productVariation);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
