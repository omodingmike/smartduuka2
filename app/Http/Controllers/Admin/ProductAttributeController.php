<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\ProductAttributeRequest;
    use App\Http\Resources\ProductAttributeResource;
    use App\Models\ProductAttribute;
    use App\Services\ProductAttributeService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;

    class ProductAttributeController extends AdminController
    {

        public ProductAttributeService $productAttributeService;

        public function __construct(ProductAttributeService $productAttributeService)
        {
            parent::__construct();
            $this->productAttributeService = $productAttributeService;
            $this->middleware( [ 'permission:settings' ] )->only( 'show' , 'store' , 'update' , 'destroy' );
        }

        public function index(PaginateRequest $request) : Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return ProductAttributeResource::collection( $this->productAttributeService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }


        public function show(ProductAttribute $productAttribute)
        {
            try {
//            return new ProductAttributeResource($this->productAttributeService->show($productAttribute));
                return response()->json( [ 'data' => $this->productAttributeService->show( $productAttribute )->productAttributeOptions->toArray() ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(ProductAttributeRequest $request) : Response | ProductAttributeResource | Application | ResponseFactory
        {
            try {
                return new ProductAttributeResource( $this->productAttributeService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }


        public function update(ProductAttributeRequest $request , ProductAttribute $productAttribute) : Response | ProductAttributeResource | Application | ResponseFactory
        {
            try {
                return new ProductAttributeResource( $this->productAttributeService->update( $request , $productAttribute ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                ProductAttribute::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
