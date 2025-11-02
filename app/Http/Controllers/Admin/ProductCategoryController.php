<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\ProductCategory;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductCategoryExport;
use App\Http\Requests\PaginateRequest;
use App\Imports\ProductCategoryImport;
use App\Http\Requests\ImportFileRequest;
use App\Services\ProductCategoryService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\ProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductCategoryDepthTreeResource;

class ProductCategoryController extends AdminController
{
    private ProductCategoryService $productCategoryService;

    public function __construct(ProductCategoryService $productCategory)
    {
        parent::__construct();
        $this->productCategoryService = $productCategory;
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show', 'export', 'downloadAttachment', 'import');
    }

    public function depthTree(): Application|\Illuminate\Http\Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return ProductCategoryDepthTreeResource::collection($this->productCategoryService->depthTree());
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return ProductCategoryResource::collection($this->productCategoryService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }


    public function store(ProductCategoryRequest $request): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return new ProductCategoryResource($this->productCategoryService->store($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show(ProductCategory $productCategory): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return new ProductCategoryResource($this->productCategoryService->show($productCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): \Illuminate\Http\Response|ProductCategoryResource|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return new ProductCategoryResource($this->productCategoryService->update($request, $productCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy(ProductCategory $productCategory): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            $this->productCategoryService->destroy($productCategory);
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function ancestorsAndSelf(ProductCategory $productCategory): Application|\Illuminate\Http\Response| AnonymousResourceCollection|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return ProductCategoryResource::collection($this->productCategoryService->ancestorsAndSelf($productCategory));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function tree()
    {
        try {
            return $this->productCategoryService->tree()->toTree();
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Contracts\Foundation\Application| ResponseFactory
    {
        try {
            return Excel::download(new ProductCategoryExport($this->productCategoryService, $request), 'ProductCategory.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function downloadAttachment()
    {
        try {
            return Response::download(public_path('/file/ProductCategoryImportSample.xlsx'));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function import(ImportFileRequest $request)
    {
        try {
            Excel::import(new ProductCategoryImport($request->file('file')), $request->file('file'));
            return response('', 202);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
