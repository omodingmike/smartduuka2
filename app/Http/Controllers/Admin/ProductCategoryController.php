<?php

    namespace App\Http\Controllers\Admin;


    use App\Exports\ProductCategoryExport;
    use App\Http\Requests\ImportFileRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\ProductCategoryRequest;
    use App\Http\Resources\ProductCategoryDepthTreeResource;
    use App\Http\Resources\ProductCategoryResource;
    use App\Imports\ProductCategoryImport;
    use App\Models\ProductCategory;
    use App\Services\ProductCategoryService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Facades\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class ProductCategoryController extends AdminController
    {
        private ProductCategoryService $productCategoryService;

        public function __construct(ProductCategoryService $productCategory)
        {
            parent::__construct();
            $this->productCategoryService = $productCategory;
            $this->middleware( [ 'permission:settings' ] )->only( 'store' , 'update' , 'destroy' , 'show' , 'export' , 'downloadAttachment' , 'import' );
        }

        public function depthTree(Request $request) : Application | \Illuminate\Http\Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return ProductCategoryDepthTreeResource::collection( $this->productCategoryService->depthTree( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function list(Request $request)
        {
            $query = ProductCategory::tree()->depthFirst()->with( 'parent_category' );
            $search = $request->query('query');
            $query->when($search, function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%");
            });
            return ProductCategoryResource::collection( $query->get() );
        }

        public function index(PaginateRequest $request) : \Illuminate\Http\Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
//            $query = ProductCategory::tree()->depthFirst()->with( 'parent_category' , 'media' , 'products' )
//            $filtered =
                return ProductCategoryResource::collection( $this->productCategoryService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(ProductCategoryRequest $request) : \Illuminate\Http\Response | ProductCategoryResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new ProductCategoryResource( $this->productCategoryService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(ProductCategory $productCategory) : \Illuminate\Http\Response | ProductCategoryResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new ProductCategoryResource( $this->productCategoryService->show( $productCategory ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(ProductCategoryRequest $request , ProductCategory $productCategory) : \Illuminate\Http\Response | ProductCategoryResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new ProductCategoryResource( $this->productCategoryService->update( $request , $productCategory ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : \Illuminate\Http\Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                ProductCategory::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function ancestorsAndSelf(ProductCategory $productCategory) : Application | \Illuminate\Http\Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return ProductCategoryResource::collection( $this->productCategoryService->ancestorsAndSelf( $productCategory ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function tree()
        {
            try {
                return $this->productCategoryService->tree()->toTree();
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return Excel::download( new ProductCategoryExport( $this->productCategoryService , $request ) , 'ProductCategory.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function downloadAttachment()
        {
            try {
                return Response::download( public_path( '/file/ProductCategoryImportSample.xlsx' ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function import(ImportFileRequest $request)
        {
            try {
                Excel::import( new ProductCategoryImport( $request->file( 'file' ) ) , $request->file( 'file' ) );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
