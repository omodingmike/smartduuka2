<?php

    namespace App\Services;


    use App\Http\Requests\ProductCategoryRequest;
    use App\Libraries\QueryExceptionLibrary;
    use App\Models\ProductCategory;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;

    class ProductCategoryService
    {
        protected array $productCateFilter = [
            'name' ,
            'slug' ,
            'description' ,
            'status' ,
            'parent_id'
        ];

        protected array $exceptFilter = [
            'excepts'
        ];


        /**
         * @throws Exception
         */
        public function ancestorsAndSelf(ProductCategory $productCategory)
        {
            try {
                return $productCategory->ancestorsAndSelf->reverse();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function depthTree(Request $request)
        {
            try {
                return ProductCategory::tree()->depthFirst()
//                    ->when($request->query, function ($query) use ($request) {
//                        $query->where('name', 'ilike', "%{$request->query}%");
//                    })
                                      ->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function tree()
        {
            try {
                return ProductCategory::tree()->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function list(Request $request)
        {
            try {
                $per_page = $request->integer( 'per_page' , 10 );
                $page     = $request->integer( 'page' , 1 );
                $paginate = $request->boolean( 'paginate' );

                $query = ProductCategory::tree()->depthFirst()->with( 'parent_category' , 'media' , 'products' )->latest();
                return $paginate ? $query->paginate( perPage: $per_page , page: $page ) : $query->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function store(ProductCategoryRequest $request)
        {
            try {
                $productCategory = ProductCategory::create( Arr::except( $request->validated() , 'parent_id' ) + [ 'slug' => Str::slug( $request->name ) , 'parent_id' => $request->parent_id == 'NULL' ? NULL : $request->parent_id ] );
                if ( $request->image ) {
                    $productCategory->addMediaFromRequest( 'image' )->toMediaCollection( 'product-category' );
                }
                return $productCategory;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function update(ProductCategoryRequest $request , ProductCategory $productCategory) : ProductCategory
        {
            try {
                $productCategory->update( Arr::except( $request->validated() , 'parent_id' ) + [ 'slug' => Str::slug( $request->name ) , 'parent_id' => $request->parent_id == 'NULL' ? NULL : $request->parent_id ] );
                if ( $request->image ) {
                    $productCategory->clearMediaCollection( 'product-category' );
                    $productCategory->addMediaFromRequest( 'image' )->toMediaCollection( 'product-category' );
                }
                return $productCategory;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function destroy(ProductCategory $productCategory) : void
        {
            try {
                $productSubCategory = ProductCategory::find( $productCategory->id )->children()->get();
                if ( ! blank( $productSubCategory ) ) {
                    throw new Exception( trans( 'all.message.resource_already_used' ) , 422 );
                }
                else {
                    $checkProduct = $productCategory->products->whereNull( 'deleted_at' );
                    if ( ! blank( $checkProduct ) ) {
                        $productCategory->delete();
                    }
                    else {
                        DB::statement( 'SET FOREIGN_KEY_CHECKS=0' );
                        $productCategory->delete();
                        DB::statement( 'SET FOREIGN_KEY_CHECKS=1' );
                    }
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( QueryExceptionLibrary::message( $exception ) , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function show(ProductCategory $productCategory) : ProductCategory
        {
            try {
                return $productCategory;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
