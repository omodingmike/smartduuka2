<?php

    namespace App\Services;


    use App\Enums\BarcodeType;
    use App\Enums\MediaEnum;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\ProductVariationRequest;
    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Traits\SaveMedia;
    use Exception;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Picqer\Barcode\BarcodeGeneratorJPG;
    use Picqer\Barcode\BarcodeGeneratorPNG;


    class ProductVariationService
    {
        use SaveMedia;

        public object   $productVariation;
        protected array $productVariationFilter = [
            'product_attribute_id' ,
            'product_attribute_option_id' ,
            'price' ,
            'sku' ,
            'parent_id' ,
            'order'
        ];

        private function variationChild($arrays , $filters) : void
        {
            if ( count( $arrays ) ) {
                foreach ( $arrays as $array ) {
                    $array->selected = in_array( $array->id , $filters );
                    $this->variationChild( $array->children , $filters );
                }
            }
        }

        private function filterChild(&$arrays , $variations) : void
        {
            if ( count( $variations ) ) {
                foreach ( $variations as $variation ) {
                    $arrays[] = $variation->id;
                    if ( count( $variation->children ) > 0 ) {
                        $this->filterChild( $arrays , $variation->children );
                    }
                }
            }
        }

        public function treeWithSelected(Request $request , Product $product)
        {
            $filters = [];
            $this->filterChild( $filters , ProductVariation::find( $request->id )->bloodline()->get()->toTree() );

            $variations = ProductVariation::with( [ 'productAttribute' , 'productAttributeOption' ] )->where( [
                'product_id' => $product->id
            ] )->tree()->get()->toTree();

            if ( $variations ) {
                $this->variationChild( $variations , $filters );
            }

            return $variations;
        }

        public function tree(Request $request , Product $product)
        {
            if ( $request->id ) {
                return ProductVariation::find( $request->id )->bloodline()->get()->toTree();
            }
            else {
                return ProductVariation::with( [ 'productAttribute' , 'productAttributeOption' ] )->where(
                    [ 'product_id' => $product->id ]
                )->tree()->get()->toTree();
            }
        }

        public function singleTree(Product $product)
        {
            $productVariations = ProductVariation::with( 'media' )->where( 'product_id' , $product->id )->where( 'sku' , '!=' , NULL )->orderBy( 'parent_id' , 'asc' )->get();
            if ( count( $productVariations ) ) {
                foreach ( $productVariations as $productVariation ) {
                    $productVariation->options = $this->nested( ProductVariation::find( $productVariation->id )->ancestorsAndSelf->load( 'productAttribute' , 'productAttributeOption' )->reverse() );
                }
            }
            return $productVariations;
        }

        private function nested($variations) : array
        {
            $array = [];
            if ( count( $variations ) ) {
                foreach ( $variations as $variation ) {
                    $array[ $variation->productAttribute?->name ] = $variation->productAttributeOption?->name;
                }
            }
            return $array;
        }


        /**
         * @throws Exception
         */
        public function initialVariation(Product $product , Request $request)
        {
            try {
                $variations = $product->variations;
                if ( count( $variations ) ) {
                    $variations = $product->variations()
                                          ->with(
                                              [ 'productAttribute:id,name' ,
                                                  'productAttributeOption:id,name' ,
                                                  'product:id,offer_start_date,offer_end_date,discount,show_stock_out,can_purchasable,unit_id,other_unit_id' ,
                                                  'product.unit:id,code' ,
                                                  'product.sellingUnits:id,code' ,
                                              ] )
                                          ->get();

                    $variations->each( function ($variation) use ($request) {
                        $variation->stock_items_sum_quantity             = $variation->stockItems()
                                                                                     ->when( $request->warehouse_id , function ($query) use ($request) {
                                                                                         return $query->where( 'warehouse_id' , $request->warehouse_id );
                                                                                     } )
                                                                                     ->selectRaw( 'SUM(quantity) as stock_items_sum_quantity' )->value( 'stock_items_sum_quantity' );
                        $variation->other_stock_items_sum_other_quantity = $variation->stockItems()
                                                                                     ->when( $request->warehouse_id , function ($query) use ($request) {
                                                                                         return $query->where( 'warehouse_id' , $request->warehouse_id );
                                                                                     } )
                                                                                     ->selectRaw( 'SUM(other_quantity) as other_stock_items_sum_other_quantity' )->value( 'stock_items_sum_quantity' );
                    } );
                    return $variations->groupBy( 'product_attribute_id' )->first();
                }
                return $variations;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function childrenVariation(ProductVariation $productVariation) : Collection
        {
            try {
                return $productVariation->children()
                                        ->with( [
                                            'productAttribute:id,name' ,
                                            'productAttributeOption:id,name' ,
                                            'product:id,units_nature,offer_start_date,offer_end_date,discount,show_stock_out,can_purchasable,unit_id,other_unit_id' ,
                                            'product.unit:id,code' ,
                                            'product.sellingUnits:id,code' ,
                                        ] )
                                        ->withSum( 'stockItems' , 'quantity' )
                                        ->withSum( 'otherStockItems' , 'other_quantity' )
                                        ->get();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function allVariation(Product $product)
        {
            return ProductVariation::tree()->depthFirst()->where( 'product_id' , $product->id )->withSum( 'stockItems' , 'quantity' )->get()->toTree();
        }

        /**
         * @throws Exception
         */
        public function ancestorsAndSelf(ProductVariation $productVariation)
        {
            try {
                return $productVariation->ancestorsAndSelf->load( 'productAttribute' , 'productAttributeOption' )->reverse();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function ancestorsAndSelfId(ProductVariation $productVariation)
        {
            try {
                $productVariations = $productVariation->ancestorsAndSelf->reverse();
                if ( count( $productVariations ) ) {
                    return $productVariation->ancestorsAndSelf->reverse()->pluck( 'id' )->toArray();
                }
                return [];
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function ancestorsToString(ProductVariation $productVariation) : string
        {
            try {
                $string = '';
                $arrays = $this->ancestorsAndSelf( $productVariation );
                if ( count( $arrays ) ) {
                    $i = 1;
                    foreach ( $arrays as $array ) {
                        if ( isset( $array[ 'productAttributeOption' ][ 'name' ] ) ) {
                            $string .= Str::ucfirst( $array[ 'productAttributeOption' ][ 'name' ] ) . ( $i != count( $arrays ) ? ' | ' : '' );
                        }
                        $i++;
                    }
                }
                return $string;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }


        /**
         * @throws Exception
         */
        public function list(PaginateRequest $request , Product $product)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';

                return ProductVariation::with( [ 'product' , 'productAttribute' , 'productAttributeOption' ] )->where( [
                    'product_id' => $product->id
                ] )->where( function ($query) use ($requests) {
                    foreach ( $requests as $key => $request ) {
                        if ( in_array( $key , $this->productVariationFilter ) ) {
                            $query->where( $key , 'like' , '%' . $request . '%' );
                        }
                    }
                } )->orderBy( $orderColumn , $orderType )->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */

        public function store(ProductVariationRequest $request , Product $product) : object
        {
            try {
                DB::beginTransaction(); // Manual control for clearer flow

                $order      = 1;
                $parentId   = NULL;
                $collection = [];

                $attributes    = json_decode( $request->product_attributes , TRUE );
                $options       = json_decode( $request->product_attribute_options , TRUE );
                $retailData    = json_decode( $request->retail_pricing , TRUE );
                $wholesaleData = json_decode( $request->wholesale_pricing , TRUE );

                $sellingPrice     = $retailData[ 0 ][ 'sellingPrice' ] ?? 0;
                $legacyVariations = [];

                // 1. COMBINE into the legacy format
                if ( is_array( $attributes ) && count( $attributes ) === count( $options ) ) {
                    foreach ( $attributes as $key => $attrId ) {
                        $legacyVariations[] = [
                            'product_attribute_id'        => (string) $attrId ,
                            'product_attribute_option_id' => $options[ $key ] ,
                            'price'                       => (string) $sellingPrice ,
                            'sku'                         => $request->sku ,
                            'retail_pricing'              => $retailData ,
                            'wholesale_pricing'           => $wholesaleData ,
                        ];
                    }
                }

                $variationsCount = count( $legacyVariations ) - 1;

                foreach ( $legacyVariations as $key => $variation ) {
                    $isLast = ( $key === $variationsCount );

                    // Check if this specific hierarchy step exists
                    $productVariation = ProductVariation::where( [
                        'product_id'                  => $product->id ,
                        'product_attribute_id'        => $variation[ 'product_attribute_id' ] ,
                        'product_attribute_option_id' => $variation[ 'product_attribute_option_id' ] ,
                        'parent_id'                   => $parentId
                    ] )->orderBy( 'id' , 'desc' )->first();

                    if ( $productVariation ) {
                        // Update existing record
                        $productVariation->update( [
                            'price' => $variation[ 'price' ] ,
                            'sku'   => $isLast ? $variation[ 'sku' ] : NULL ,
                        ] );
                    }
                    else {
                        // Calculate order for new record
                        $lastVariationInLevel = ProductVariation::where( [
                            'product_id'           => $product->id ,
                            'product_attribute_id' => $variation[ 'product_attribute_id' ] ,
                        ] )->max( 'order' );

                        $order = ( $lastVariationInLevel ?? 0 ) + 1;

                        // Create new record
                        $productVariation = ProductVariation::create( [
                            'product_id'                  => $product->id ,
                            'product_attribute_id'        => $variation[ 'product_attribute_id' ] ,
                            'product_attribute_option_id' => $variation[ 'product_attribute_option_id' ] ,
                            'price'                       => $variation[ 'price' ] ,
                            'sku'                         => $isLast ? $variation[ 'sku' ] : NULL ,
                            'parent_id'                   => $parentId ,
                            'order'                       => $order
                        ] );
                    }

                    $parentId     = $productVariation->id;
                    $collection[] = $productVariation;

                    // Only run pricing and media for the leaf (final) variation
                    if ( $isLast ) {
                        // Store Retail Pricing
                        if ( ! empty( $variation[ 'retail_pricing' ][ 0 ] ) ) {
                            $retailItem = $variation[ 'retail_pricing' ][ 0 ];
                            $productVariation->retailPrices()->updateOrCreate(
                                [ 'unit_id' => $retailItem[ 'unitId' ] ?? NULL ] ,
                                [
                                    'buying_price'  => $retailItem[ 'buyingPrice' ] ?? 0 ,
                                    'selling_price' => $retailItem[ 'sellingPrice' ] ?? 0 ,
                                ]
                            );
                        }

                        // Store Wholesale Tiers
                        if ( ! empty( $variation[ 'wholesale_pricing' ] ) ) {
                            // Consider clearing old tiers if this is an update logic
                            $productVariation->wholesalePrices()->delete();
                            foreach ( $variation[ 'wholesale_pricing' ] as $tier ) {
                                $productVariation->wholesalePrices()->create( [
                                    'minQuantity' => $tier[ 'minQuantity' ] ?? NULL ,
                                    'price'       => $tier[ 'price' ] ?? 0 ,
                                ] );
                            }
                        }
                        $this->saveMedia( $request , $productVariation , MediaEnum::IMAGES_COLLECTION );
                    }
                }

                // Update main product minimum price
                $minPrice = ProductVariation::where( 'product_id' , $product->id )->min( 'price' );
                if ( $minPrice ) {
                    $product->update( [ 'variation_price' => $minPrice ] );
                }

                DB::commit();
                return collect( $collection );

            } catch ( \Exception $exception ) {
                DB::rollBack();
                Log::error( 'Product Variation Store Error: ' . $exception->getMessage() );
                throw new \Exception( $exception->getMessage() , 422 );
            }
        }

        private function recursiveDelete($variation) : void
        {
            if ( $variation->sku == NULL && count( $variation->children ) == 0 ) {
                $productId = $variation->product_id;
                $variation->delete();

                $treeVariations = ProductVariation::where( [ 'product_id' => $productId ] )->tree()->get()->toTree();
                foreach ( $treeVariations as $treeVariation ) {
                    $this->recursiveDelete( $treeVariation );
                }
            }
        }


        /**
         * @throws Exception
         */
        public function update(ProductVariationRequest $request , Product $product , ProductVariation $productVariation) : object
        {
            try {
                DB::transaction( function () use ($request , $product , $productVariation) {
                    $order           = 1;
                    $parentId        = NULL;
                    $collection      = [];
                    $variations      = json_decode( $request->attribute );
                    $variationsCount = ( count( $variations ) - 1 );

                    if ( is_array( $variations ) ) {
                        $oldProductVariationsCount = ProductVariation::find( $productVariation->id )->bloodline()->get()->count();

                        foreach ( $variations as $key => $variation ) {
                            $productVariationExistCheck = ProductVariation::where( [
                                'product_id'                  => $product->id ,
                                'product_attribute_id'        => $variation->product_attribute_id ,
                                'product_attribute_option_id' => $variation->product_attribute_option_id ,
                                'parent_id'                   => $parentId
                            ] )->orderBy( 'id' , 'desc' )->first();

                            $productVariationOrderCheck = ProductVariation::where( [
                                'product_id'           => $product->id ,
                                'product_attribute_id' => $variation->product_attribute_id ,
                            ] )->orderBy( 'id' , 'desc' )->first();

                            if ( $productVariationOrderCheck ) {
                                $order = $productVariationOrderCheck->order + 1;
                            }

                            if ( $productVariationExistCheck ) {
                                $productVariationExistCheck->update( [ 'price' => $variation->price ] );

                                if ( $key != $variationsCount ) {
                                    $productVariationExistCheck->update( [ 'sku' => NULL ] );
                                }
                                else {
                                    $productVariationExistCheck->update( [ 'sku' => $variation->sku ] );

                                    $generator = new BarcodeGeneratorPNG();
                                    $barcode   = NULL;
                                    $black     = [ 0 , 0 , 0 ];
                                    if ( $product->barcode_id === BarcodeType::EAN_13 ) {
                                        $barcode_value = str_pad( $request->sku , 12 , '0' , STR_PAD_LEFT );
                                        $barcode       = $generator->getBarcode( $barcode_value , $generator::TYPE_EAN_13 , 3 , 50 , $black );
                                    }
                                    if ( $product->barcode_id === BarcodeType::UPC_A ) {
                                        $barcode_value = str_pad( $request->sku , 11 , '0' , STR_PAD_LEFT );
                                        $barcode       = $generator->getBarcode( $barcode_value , $generator::TYPE_UPC_A , 3 , 50 , $black );
                                    }
                                    if ( $request->user_barcode ) {
                                        $barcode_value                  = $request->user_barcode;
                                        $productVariation->user_barcode = $barcode_value;
                                        $productVariation->save();
                                    }

                                    if ( $barcode ) {
                                        $tempFilePath = storage_path( 'app/public/barcode.png' );
                                        file_put_contents( $tempFilePath , $barcode );
                                        $productVariation->clearMediaCollection( 'product-variation-barcode' );
                                        $productVariationExistCheck->addMedia( $tempFilePath )->toMediaCollection( 'product-variation-barcode' );
                                    }
                                }

                                $parentId     = $productVariationExistCheck->id;
                                $collection[] = $productVariationExistCheck;
                                continue;
                            }
                            else {
                                if ( $key == $variationsCount && ( $variationsCount + 1 ) == $oldProductVariationsCount ) {
                                    $productVariation->update( [
                                        'product_attribute_id'        => $variation->product_attribute_id ,
                                        'product_attribute_option_id' => $variation->product_attribute_option_id ,
                                        'price'                       => $variation->price ,
                                        'parent_id'                   => $parentId ,
                                        'order'                       => $order ,
                                        'sku'                         => $variation->sku
                                    ] );
                                    $collection[] = $productVariation;
                                    continue;
                                }
                            }

                            $sku = AppLibrary::sku( rand( 1000000 , 9999999 ) );

                            $createProductVariation = ProductVariation::create( [
                                'product_id'                  => $product->id ,
                                'product_attribute_id'        => $variation->product_attribute_id ,
                                'product_attribute_option_id' => $variation->product_attribute_option_id ,
                                'price'                       => $variation->price ,
                                'sku'                         => ( $key == $variationsCount ? $sku : NULL ) ,
                                'parent_id'                   => $parentId ,
                                'order'                       => $order
                            ] );
                            $parentId               = $createProductVariation->id;
                            $collection[]           = $createProductVariation;

                            if ( $key == $variationsCount ) {
                                $generator = new BarcodeGeneratorJPG();

                                if ( $product->barcode_id === BarcodeType::EAN_13 ) {
                                    $barcode_value = str_pad( $sku , 12 , '0' , STR_PAD_LEFT );
                                    $barcode       = $generator->getBarcode( $barcode_value , $generator::TYPE_EAN_13 );
                                }
                                if ( $product->barcode_id === BarcodeType::UPC_A ) {
                                    $barcode_value = str_pad( $sku , 11 , '0' , STR_PAD_LEFT );
                                    $barcode       = $generator->getBarcode( $barcode_value , $generator::TYPE_UPC_A );
                                }

                                if ( $barcode ) {
                                    $tempFilePath = storage_path( 'app/public/barcode.jpg' );
                                    file_put_contents( $tempFilePath , $barcode );
                                    $productVariation->clearMediaCollection( 'product-variation-barcode' );
                                    $createProductVariation->addMedia( $tempFilePath )->toMediaCollection( 'product-variation-barcode' );
                                }
                            }
                        }

                        $treeVariations = ProductVariation::where( [ 'product_id' => $product->id ] )->tree()->get()->toTree();
                        foreach ( $treeVariations as $treeVariation ) {
                            $this->recursiveDelete( $treeVariation );
                        }

                        $this->productVariation = collect( $collection );
                        $productData            = Product::find( $product->id );
                        $checkMinPrice          = $product->variations->min( 'price' );
                        if ( $checkMinPrice ) {
                            $productData->variation_price = $checkMinPrice;
                            $productData->save();
                        }
                    }
                } );
                return $this->productVariation;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                DB::rollBack();
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        /**
         * @throws Exception
         */
        public function destroy(Product $product, ProductVariation $productVariation) : void
        {
            try {
                DB::transaction(function () use ($product, $productVariation) {
                    // Check if the variation belongs to the product and is a leaf node (has SKU)
                    if ($productVariation->product_id == $product->id && $productVariation->sku != NULL) {

                        // 1. Delete associated pricing records created in store()
                        $productVariation->wholesalePrices()->delete();
                        $productVariation->retailPrices()->delete();

                        // 2. Explicitly clear media collections to remove files from storage
                        $productVariation->clearMediaCollection(MediaEnum::IMAGES_COLLECTION);
                        $productVariation->clearMediaCollection('product-variation-barcode');

                        // 3. Store parent_id before deletion for recursive cleanup
                        $parentId = $productVariation->parent_id;

                        // 4. Delete the variation record
                        $productVariation->delete();

                        // 5. Clean up parent hierarchy if they have no other children
                        if ($parentId) {
                            $this->parentDelete($parentId);
                        }

                        // 6. Update the main product's minimum variation price
                        $productData = Product::find($product->id);
                        $checkMinPrice = $productData->variations()->whereNotNull('sku')->min('price');

                        // Update with new min price or set to 0 if no variations remain
                        $productData->variation_price = $checkMinPrice ?? 0;
                        $productData->save();
                    }
                });
            } catch (Exception $exception) {
                Log::error('Product Variation Destroy Error: ' . $exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        private function parentDelete($id) : void
        {
            $parentVariation = ProductVariation::where( [ 'id' => $id ] )->orderBy( 'id' , 'desc' )->first();
            if ( $parentVariation && blank( $parentVariation->children()->get() ) ) {
                $parentVariation->delete();
                $this->parentDelete( $parentVariation->parent_id );
            }
        }

        /**
         * @throws Exception
         */
        public function show(Product $product , ProductVariation $productVariation)
        {
            try {
                return ProductVariation::where( [ 'product_id' => $product->id , 'id' => $productVariation->id ] )->first();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function barcodeVariationProduct(ProductVariation $productVariation)
        {
            try {
                return ProductVariation::where( 'id' , $productVariation->id )->with( [ 'productAttribute:id,name' , 'productAttributeOption:id,name' , 'product:id,offer_start_date,offer_end_date,discount,show_stock_out,can_purchasable' ] )->withSum( 'stockItems' , 'quantity' )->first();
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function downloadBarcode(ProductVariation $productVariation)
        {
            return $productVariation->getMedia( 'product-variation-barcode' )->first();
        }
    }
