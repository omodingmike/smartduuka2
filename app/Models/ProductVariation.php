<?php

    namespace App\Models;

    use App\Enums\MediaEnum;
    use App\Enums\Status;
    use App\Enums\StockStatus;
    use App\Traits\HasImageMedia;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;
    use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    class ProductVariation extends Model implements HasMedia
    {
        use HasRecursiveRelationships ,  HasImageMedia;

        protected $table    = "product_variations";
        protected $fillable = [
            'product_id' ,
            'product_attribute_id' ,
            'product_attribute_option_id' ,
            'price' ,
            'sku' ,
            'parent_id' ,
            'order'
        ];
        protected $appends  = [ 'stock' ];

        protected function getMediaCollection() : string
        {
            return MediaEnum::PRODUCTS_MEDIA_COLLECTION;
        }

        public function getMediaUrlAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'product-variation-barcode' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'product-variation-barcode' ) );
            }
            return '';
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

        public function registerMediaConversions(Media $media = NULL) : void
        {
            $this->addMediaConversion( 'thumb' )->crop( 168 , 180 )->keepOriginalImageFormat()->sharpen( 10 );
            $this->addMediaConversion( 'cover' )->crop( 372 , 405 )->keepOriginalImageFormat()->sharpen( 10 );
            $this->addMediaConversion( 'preview' )->crop( 768 , 768 )->keepOriginalImageFormat()->sharpen( 10 );
        }

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class );
        }

        public function wholesalePrices() : MorphMany
        {
            return $this->morphMany( WholeSalePrice::class , 'item' );
        }

        public function retailPrices() : MorphMany
        {
            return $this->morphMany( RetailPrice::class , 'item' );
        }

        public function getStockAttribute() : float
        {
            return (float) $this->stocks()
                                ->where( 'status' , StockStatus::RECEIVED )
                                ->sum( 'quantity' );
        }

        public function getBarcodeImageAttribute() : string
        {
            if ( ! empty( $this->getFirstMediaUrl( 'product-barcode' ) ) ) {
                return asset( $this->getFirstMediaUrl( 'product-barcode' ) );
            }
            return '';
        }

        public function stocks() : MorphMany
        {
            return $this->morphMany( Stock::class , 'item' );
        }

        public function stockItems() : MorphMany
        {
            return $this->stocks()->where( 'status' , Status::ACTIVE );
        }
        public function productAttribute(): BelongsTo
        {
            return $this->belongsTo(ProductAttribute::class);
        }

        public function productAttributeOption(): BelongsTo
        {
            return $this->belongsTo(ProductAttributeOption::class);
        }

        public function otherStockItems() : MorphMany
        {
            return $this->stocks()->where( 'status' , Status::ACTIVE );
        }
        public function productAttributeOptions()
        {
            // Adjust 'product_variation_options' to match your actual pivot table name
            return $this->belongsToMany(ProductAttributeOption::class, 'product_variation_options');
        }

        // Keep existing productAttributeOption (singular) if used for single-attribute logic

        public function orderProducts() : MorphMany
        {
            return $this->morphMany( OrderProduct::class , 'item' );
        }
    }
