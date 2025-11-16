<?php

    namespace App\Http\Controllers\Admin;


    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\ProductBrandRequest;
    use App\Http\Resources\ProductBrandResource;
    use App\Models\ProductBrand;
    use App\Services\ProductBrandService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;

    class ProductBrandController extends AdminController
    {
        private ProductBrandService $productBrandService;

        public function __construct(ProductBrandService $productBrandService)
        {
            parent::__construct();
            $this->productBrandService = $productBrandService;
            $this->middleware( [ 'permission:settings' ] )->only( 'store' , 'update' , 'destroy' , 'show' );
        }

        public function index(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                $filtered = $this->filter( new ProductBrand() , $request , [ 'name' ] );
                return ProductBrandResource::collection( $filtered );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function list(Request $request): Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                $query = ProductBrand::query();
                $search = $request->query('query');
                $query->when($search, function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%");
                });
                return ProductBrandResource::collection($query->get());
            } catch (Exception $exception) {
                return response([
                    'status'  => false,
                    'message' => $exception->getMessage()
                ], 422);
            }
        }



        public function store(ProductBrandRequest $request) : Response | ProductBrandResource | Application | ResponseFactory
        {
            try {
                return new ProductBrandResource( $this->productBrandService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(
            ProductBrand $productBrand
        ) : Response | ProductBrandResource | Application | ResponseFactory
        {
            try {
                return new ProductBrandResource( $this->productBrandService->show( $productBrand ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(
            ProductBrandRequest $request ,
            ProductBrand $productBrand
        ) : Response | ProductBrandResource | Application | ResponseFactory
        {
            try {
                return new ProductBrandResource( $this->productBrandService->update( $request , $productBrand ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request)
        {
            try {
                ProductBrand::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
