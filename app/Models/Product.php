<?php

    namespace App\Models;

    use App\Enums\MediaEnum;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Http\Resources\RetailPriceResource;
    use App\Http\Resources\WholeSalePriceResource;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\hasOne;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\Auth;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    class Product extends Model implements HasMedia
    {
        use HasFactory , SoftDeletes , HasImageMedia;

        protected       $table   = 'products';
        protected       $guarded = [];
        protected       $appends = [ 'stock' , 'single_tree','image' ];
        protected array $dates   = [ 'deleted_at' ];
        protected       $casts   = [
            'id'                         => 'integer' ,
            'name'                       => 'string' ,
            'slug'                       => 'string' ,
            'sku'                        => 'string' ,
            'product_category_id'        => 'integer' ,
            'product_brand_id'           => 'integer' ,
            'barcode_id'                 => 'integer' ,
            'unit_id'                    => 'integer' ,
            'buying_price'               => 'integer' ,
            'selling_price'              => 'integer' ,
            'variation_price'            => 'decimal:6' ,
            'status'                     => Status::class ,
            'order'                      => 'integer' ,
            'can_purchasable'            => 'integer' ,
            'show_stock_out'             => 'integer' ,
            'maximum_purchase_quantity'  => 'integer' ,
            'low_stock_quantity_warning' => 'integer' ,
            'weight'                     => 'string' ,
            'refundable'                 => 'integer' ,
            'description'                => 'string' ,
            'add_to_flash_sale'          => 'integer' ,
            'discount'                   => 'decimal:6' ,
            'offer_start_date'           => 'string' ,
            'offer_end_date'             => 'string' ,
        ];

        protected function getMediaCollection() : string
        {
            return MediaEnum::PRODUCTS_MEDIA_COLLECTION;
        }

        public function scopeActive($query , $col = 'status')
        {
            return $query->where( $col , Status::ACTIVE );
        }

        public function getStockAttribute() : float
        {
            return (float) $this->stocks()
                                ->where( 'status' , StockStatus::RECEIVED )
                                ->sum( 'quantity' );
        }

        public function scopeRandAndLimitOrOrderBy($query , $rand = 0 , $orderColumn = 'id' , $orderType = 'asc')
        {
            if ( $rand > 0 ) {
                return $query->inRandomOrder()->limit( $rand );
            }
            return $query->orderBy( $orderColumn , $orderType );
        }

        public function getImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                return asset( $this->getFirstMediaUrl( $this->getMediaCollection() ) );
            }
            return asset( 'images/default/product/thumb.png' );
        }

        public function getImagesAttribute() : array
        {
            $response = [];
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                $images = $this->getMedia( $this->getMediaCollection() );
                foreach ( $images as $image ) {
                    $response[] = $image[ 'original_url' ];
                }
            }
            return $response;
        }

        public function getThumbAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                $product = $this->getMedia( $this->getMediaCollection() )->first();
                return $product->getUrl( 'thumb' );
            }
            return asset( 'images/default/product/thumb.png' );
        }

        public function getCoverAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                $product = $this->getMedia( $this->getMediaCollection() )->first();
                return $product->getUrl( 'cover' );
            }
            return asset( 'images/default/product/cover.png' );
        }

        public function getPreviewAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                $product = $this->getMedia( $this->getMediaCollection() )->first();
                return $product->getUrl( 'preview' );
            }
            return asset( 'images/default/product/preview.png' );
        }

        public function getPreviewsAttribute() : array
        {
            $response = [];
            if ( ! empty( $this->getFirstMediaUrl( $this->getMediaCollection() ) ) ) {
                $images = $this->getMedia( $this->getMediaCollection() );
                foreach ( $images as $image ) {
                    $response[] = $image->getUrl( 'preview' );
                }
            }
            return $response;
        }

        public function getBarcodeImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'product-barcode' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'product-barcode' ) );
            }
            return '';
        }

        public function registerMediaConversions(Media $media = NULL) : void
        {
            $this->addMediaConversion( 'thumb' )->crop( 168 , 180 )->keepOriginalImageFormat()->sharpen( 10 );
            $this->addMediaConversion( 'cover' )->crop( 372 , 405 )->keepOriginalImageFormat()->sharpen( 10 );
            $this->addMediaConversion( 'preview' )->crop( 768 , 768 )->keepOriginalImageFormat()->sharpen( 10 );
        }

        public function category() : BelongsTo
        {
            return $this->belongsTo( ProductCategory::class , 'product_category_id' , 'id' );
        }

        public function brand() : BelongsTo
        {
            return $this->belongsTo( ProductBrand::class , 'product_brand_id' , 'id' );
        }

        public function barcode() : BelongsTo
        {
            return $this->belongsTo( Barcode::class , 'barcode_id' , 'id' );
        }

        public function unit() : BelongsTo
        {
            return $this->belongsTo( Unit::class , 'unit_id' , 'id' );
        }

        public function sellingUnits() : BelongsToMany
        {
            return $this->belongsToMany( Unit::class , 'product_units' , 'product_id' , 'unit_id' );
        }

        public function wholesalePrices() : MorphMany
        {
            return $this->morphMany( WholeSalePrice::class , 'item' );
        }

        public function retailPriceUpdates() : MorphMany
        {
            return $this->morphMany( RetailPriceUpdate::class , 'item' );
        }

        public function wholesalePriceUpdates() : MorphMany
        {
            return $this->morphMany( WholesalePriceUpdate::class , 'item' );
        }

        public function retailPrices() : MorphMany
        {
            return $this->morphMany( RetailPrice::class , 'item' );
        }

//        public function prices() : Builder | HasMany | Product
//        {
//            return $this->hasMany( RetailPrice::class , 'product_id' , 'id' );
//        }

        public function commissionTargets() : Builder | HasMany | Product
        {
            return $this->hasMany( CommissionTarget::class );
        }


        public function variations() : HasMany
        {
            return $this->hasMany( ProductVariation::class , 'product_id' , 'id' );
        }

        public function variations1() : HasMany
        {
            return $this->hasMany( ProductVariation::class , 'product_id' , 'id' )->with( 'productAttribute' );
        }

        public function orders() : MorphMany
        {
            return $this->morphMany( Stock::class , 'model' );
        }

        public function orderCountable() : HasMany
        {
            return $this->hasMany( Stock::class , 'product_id' , 'id' );
        }


        private function generateNestedOptions($variation) : array
        {
            $options = [];
            // Load ancestors and self using the Adjacency List relationship
            $ancestors = $variation->ancestorsAndSelf()
                                   ->with( [ 'productAttribute' , 'productAttributeOption' ] )
                                   ->get()
                                   ->reverse();

            foreach ( $ancestors as $node ) {
                if ( $node->productAttribute && $node->productAttributeOption ) {
//                    $options[ $node->productAttribute->name ] = $node->productAttributeOption->name;
                    $options[ 'name' ]   = $node->productAttribute->name;
                    $options[ 'option' ] = $node->productAttributeOption->name;
                }
            }

            return $options;
        }

        public function getSingleTreeAttribute()
        {
            // Fetch variations with necessary relations including retailPrices for buying_price
            $productVariations = ProductVariation::with( [
                'media' ,
                'productAttribute' ,
                'productAttributeOption' ,
                'wholesalePrices' ,
//                'retailPrices'
            ] )
                                                 ->where( 'product_id' , $this->id )
                                                 ->whereNotNull( 'sku' )
                                                 ->get();

            return $productVariations->map( function ($variation) {
                // 1. Build the options path
                $options = [];
                $nodes   = $variation->ancestorsAndSelf()
                                     ->with( [ 'productAttribute' , 'productAttributeOption' ] )
                                     ->get()
                                     ->reverse();

                foreach ( $nodes as $node ) {
                    if ( $node->productAttribute && $node->productAttributeOption ) {
                        $options[ $node->productAttribute->name ] = $node->productAttributeOption->name;
                    }
                }

                // 2. Initialize $data FIRST so we don't overwrite custom keys
                $data = $variation->toArray();

                // 3. PRICE MANIPULATION (Discount & Formatting)
                $originalPrice = (float) $variation->price;
                $discount      = (float) $this->discount;
                $isOffer       = FALSE;

                // Date validation: Check if 'now' is between start and end dates
                $now   = now();
                $start = $this->offer_start_date ? \Carbon\Carbon::parse( $this->offer_start_date ) : NULL;
                $end   = $this->offer_end_date ? \Carbon\Carbon::parse( $this->offer_end_date ) : NULL;

                if ( $discount > 0 &&
                    ( ! $start || $now->greaterThanOrEqualTo( $start ) ) &&
                    ( ! $end || $now->lessThanOrEqualTo( $end ) ) ) {
                    $isOffer = TRUE;
                }

                // Calculate discounted price (assuming percentage)
                $discountedPrice = $isOffer ? ( $originalPrice - ( $originalPrice * ( $discount / 100 ) ) ) : $originalPrice;

                // Inject manipulated prices into the array
                $data[ 'price' ]            = $originalPrice;
                $data[ 'price_currency' ]   = currency( $originalPrice , 2 , '.' , '' );
                $data[ 'discounted_price' ] = number_format( $discountedPrice , 2 , '.' , '' );
                $data[ 'is_offer' ]         = $isOffer;

                // Include buying price from the retail_prices relation
                $retail                 = $variation->retailPrices->first();
                $data[ 'buying_price' ] = $retail ? number_format( (float) $retail->buying_price , 2 , '.' , '' ) : '0.00';

                // 4. VARIATION NAME FORMATTING
                $formattedOptions = [];
                foreach ( $options as $key => $value ) {
                    $formattedOptions[] = "$key :: $value";
                }
                $data[ 'variation_name' ] = implode( ' > ' , $formattedOptions );

                // 5. STOCK & OPTIONS
                $data[ 'options' ] = $options;
                $data[ 'stock' ]   = (float) $variation->stock;

                // 6. SINGLE MEDIA OBJECT
                $mediaItem       = $variation->getFirstMedia( 'product-variation-image' )
                    ?? $variation->media->first();
                $data[ 'media' ] = $mediaItem ? $mediaItem : NULL;

                // 7. WHOLESALE TIERS
                $data[ 'wholesale_prices' ] = WholeSalePriceResource::collection( $variation->wholesalePrices );

                return $data;
            } )->values();
        }

        public function getSingleTreeAttribute1()
        {
            $productVariations = $this->variations()->with( 'media' )->where( 'sku' , '!=' , NULL )->orderBy( 'parent_id' , 'asc' )
                                      ->get();
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

        public function getSingleTreeAttribute2()
        {
            $productVariations = ProductVariation::with( [
                'media' , 'productAttribute' , 'productAttributeOption' , 'wholesalePrices' , 'retailPrices'
            ] )
                                                 ->where( 'product_id' , $this->id )
                                                 ->whereNotNull( 'sku' )
                                                 ->get();

            return $productVariations->map( function ($variation) {
                // Build the option path (Material > Color > Size)
                $options = [];
                $nodes   = $variation->ancestorsAndSelf()->with( [ 'productAttribute' , 'productAttributeOption' ] )->get()->reverse();
                foreach ( $nodes as $node ) {
                    if ( $node->productAttribute && $node->productAttributeOption ) {
                        $options[ $node->productAttribute->name ] = $node->productAttributeOption->name;
                    }
                }

                $data              = $variation->toArray();
                $data[ 'options' ] = $options;
                $data[ 'media' ]   = $variation->media;
                $data[ 'stock' ]   = (float) $variation->stock;

                // Wholesale: Multiple records (Array)
                $data[ 'wholesale_prices' ] = WholeSalePriceResource::collection( $variation->wholesalePrices );

                // Retail: Single record (Object)
                $retail                 = $variation->retailPrices->first();
                $data[ 'retail_price' ] = $retail ? new RetailPriceResource( $retail ) : NULL;

                return $data;
            } )->values();
        }

        public function tags() : HasMany
        {
            return $this->hasMany( ProductTag::class , 'product_id' , 'id' );
        }

        public function reviews() : HasMany
        {
            return $this->hasMany( ProductReview::class , 'product_id' , 'id' );
        }

        public function scopeWithReviewRating($query)
        {
            $reviewsStar      = ProductReview::selectRaw( 'sum(star)' )->whereColumn( 'product_id' , 'products.id' )->getQuery();
            $reviewsStarCount = ProductReview::selectRaw( 'count(product_id)' )->whereColumn( 'product_id' , 'products.id' )->getQuery();
            $base             = $query->getQuery();
            if ( is_null( $base->columns ) ) {
                $query->select( [ $base->from . '.*' ] );
            }
            return $query->selectSub( $reviewsStar , 'rating_star' )->selectSub( $reviewsStarCount , 'rating_star_count' );
        }

        public function averageRating()
        {
            return $this->reviews()->avg( 'star' );
        }

        public function reviewCount() : int
        {
            return $this->reviews()->count();
        }

        public function stocks() : MorphMany
        {
            return $this->morphMany( Stock::class , 'item' );
        }

        public function stockItems() : MorphMany
        {
            return $this->stocks()->where( 'status' , Status::ACTIVE );
        }

        public function taxes() : HasMany
        {
            return $this->hasMany( ProductTax::class , 'product_id' , 'id' );
        }

        public function productTaxes() : HasMany
        {
            return $this->hasMany( ProductTax::class );
        }

        public function rawMaterials() : BelongsToMany
        {
            return $this->belongsToMany( Ingredient::class , 'item_raw_materials' , 'product_id' , 'ingredient_id' )->withPivot( [ 'quantity' , 'buying_price' ,
                'total' , 'setup_id'
            ] );
        }

        public function productOrders() : HasMany
        {
            return $this->hasMany( Stock::class , 'product_id' , 'id' )->where( 'model_type' , Order::class );
        }

        public function userReview() : hasOne
        {
            return $this->hasOne( ProductReview::class , 'product_id' , 'id' )->where( 'user_id' , Auth::user()->id );
        }

        public function orderProducts() : MorphMany
        {
            return $this->morphMany( OrderProduct::class , 'item' );
        }
    }
