<?php

    namespace App\Http\Controllers\Admin;

    use App\Exports\ProductExport;
    use App\Http\Requests\ChangeImageRequest;
    use App\Http\Requests\ImportFileRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\ProductOfferRequest;
    use App\Http\Requests\ProductRequest;
    use App\Http\Resources\IngredientResource;
    use App\Http\Resources\ProductAdminResource;
    use App\Http\Resources\ProductDetailsAdminResource;
    use App\Http\Resources\SimpleProductDetailsResource;
    use App\Http\Resources\simpleProductWithVariationCountResource;
    use App\Imports\ProductImport;
    use App\Models\Product;
    use App\Services\ProductService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class ProductController extends AdminController
    {
        public ProductService $productService;

        public function __construct(ProductService $productService)
        {
            parent::__construct();
            $this->productService = $productService;
            $this->middleware([ 'permission:products' ])->only('export' , 'generateSku' , 'downloadAttachment');
            $this->middleware([ 'permission:products_create' ])->only('store' , 'uploadImage' , 'import');
            $this->middleware([ 'permission:products_edit' ])->only('update');
            $this->middleware([ 'permission:products_delete' ])->only('destroy' , 'deleteImage');
            $this->middleware([ 'permission:products_show' ])->only('show' , 'downloadBarcode');
        }

        public function index(PaginateRequest $request) : \Illuminate\Http\Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return ProductAdminResource::collection($this->productService->list($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function purchasableIngredients(PaginateRequest $request)
        {
            try {
                return IngredientResource::collection($this->productService->purchasableIngredientsList($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }


        public function show(Product $product) : \Illuminate\Foundation\Application | \Illuminate\Http\Response | ProductDetailsAdminResource | Application | ResponseFactory
        {
            try {
                return new ProductDetailsAdminResource($this->productService->show($product));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function store(ProductRequest $request) : \Illuminate\Http\Response | ProductAdminResource | Application | ResponseFactory
        {
            try {
                return new ProductAdminResource($this->productService->store($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function update(ProductRequest $request , Product $product) : \Illuminate\Http\Response | ProductAdminResource | Application | ResponseFactory
        {

            try {
                return new ProductAdminResource($this->productService->update($request , $product));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function destroy(Product $product) : \Illuminate\Http\Response | Application | ResponseFactory
        {
            try {
                $this->productService->destroy($product);
                return response('' , 202);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function uploadImage(ChangeImageRequest $request , Product $product) : \Illuminate\Foundation\Application | \Illuminate\Http\Response | ProductDetailsAdminResource | Application | ResponseFactory
        {
            try {
                return new ProductDetailsAdminResource($this->productService->uploadImage($request , $product));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function deleteImage(Product $product , $index) : \Illuminate\Foundation\Application | \Illuminate\Http\Response | ProductDetailsAdminResource | Application | ResponseFactory
        {
            try {
                return new ProductDetailsAdminResource($this->productService->deleteImage($product , $index));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
        {
            try {
                return Excel::download(new ProductExport($this->productService , $request) , 'Product.xlsx');
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function generateSku($barcodeMethod) : \Illuminate\Foundation\Application | \Illuminate\Http\Response | Application | ResponseFactory
        {
            try {
                return response([ 'data' => [ 'product_sku' => $this->productService->generateSku($barcodeMethod) ] ] , 200);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function productOffer(ProductOfferRequest $request , Product $product) : \Illuminate\Foundation\Application | \Illuminate\Http\Response | ProductAdminResource | Application | ResponseFactory
        {
            try {
                return new ProductAdminResource($this->productService->productOffer($request , $product));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function purchasableProducts() : \Illuminate\Foundation\Application | \Illuminate\Http\Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return simpleProductWithVariationCountResource::collection($this->productService->purchasableProducts());
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function simpleProducts() : \Illuminate\Foundation\Application | \Illuminate\Http\Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return simpleProductWithVariationCountResource::collection($this->productService->simpleProducts());
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function posProduct(Product $product , Request $request) : SimpleProductDetailsResource | \Illuminate\Foundation\Application | \Illuminate\Http\Response | Application | ResponseFactory
        {
            try {
                return new SimpleProductDetailsResource($this->productService->showWithRelation($product , $request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function downloadAttachment()
        {
            try {
                return Response::download(public_path('/file/ProductImportSample.xlsx'));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function import(ImportFileRequest $request)
        {
            try {
                Excel::import(new ProductImport($request->file('file')), $request->file('file'));
                return response('', 202);
            } catch (Exception $exception) {
                return response(['status' => false, 'message' => $exception->getMessage()], 422);
            }
        }

        public function downloadBarcode(Product $product)
        {
            try {
                return $this->productService->downloadBarcode($product);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function barcodeProduct($barcode)
        {
            try {
                return response([ 'data' => $this->productService->barcodeProduct($barcode) ]);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }
    }
