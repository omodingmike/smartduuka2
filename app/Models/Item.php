<?php

    namespace App\Models;

    use App\Enums\Status;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Spatie\MediaLibrary\HasMedia;
    use Spatie\MediaLibrary\InteractsWithMedia;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    class Item extends Model implements HasMedia
    {
        use HasFactory , InteractsWithMedia , SoftDeletes;

        protected $table    = "items";
        protected $fillable = [
            'name' ,
            'item_category_id' ,
            'slug' ,
            'tax_id' ,
            'item_type' ,
            'price' ,
            'is_featured' ,
            'description' ,
            'caution' ,
            'status' ,
            'order' ,
            'overall_cost' , 'item_type' ,
            'creator_type' , 'creator_id' , 'editor_type' , 'editor_id' , 'is_stockable' , 'buying_price' , 'registerMediaConversionsUsingModelInstance'
        ];
        protected $dates    = [ 'deleted_at' ];
        protected $casts    = [
            'id'               => 'integer' ,
            'name'             => 'string' ,
            'item_category_id' => 'integer' ,
            'slug'             => 'string' ,
            'tax_id'           => 'integer' ,
            'item_type'        => 'integer' ,
            'price'            => 'decimal:6' ,
            'is_featured'      => 'integer' ,
            'description'      => 'string' ,
            'caution'          => 'string' ,
            'status'           => Status::class ,
            'order'            => 'integer' ,
            'buying_price'     => 'integer' ,
        ];

        public function getThumbAttribute() : string
        {
            if ( ! empty($this->getFirstMediaUrl('item')) ) {
                $item = $this->getMedia('item')->last();
                return $item->getUrl('thumb');
            }
            return asset('images/item/thumb.png');
        }

        public function getCoverAttribute() : string
        {
            if ( ! empty($this->getFirstMediaUrl('item')) ) {
                $item = $this->getMedia('item')->last();
                return $item->getUrl('cover');
            }
            return asset('images/item/cover.png');
        }

        public function getPreviewAttribute() : string
        {
            if ( ! empty($this->getFirstMediaUrl('item')) ) {
                $item = $this->getMedia('item')->last();
                return $item->getUrl('preview');
            }
            return asset('images/item/cover.png');
        }

        public function registerMediaConversions(Media $media = null) : void
        {
            $this->addMediaConversion('thumb')->crop('crop-center' , 112 , 120)->keepOriginalImageFormat()->sharpen(10);
            $this->addMediaConversion('cover')->crop('crop-center' , 260 , 180)->keepOriginalImageFormat()->sharpen(10);
            $this->addMediaConversion('preview')->width(400)->keepOriginalImageFormat()->sharpen(10);
        }

        public function variations() : HasMany
        {
            return $this->hasMany(ItemVariation::class)->with([ 'itemAttribute' , 'ingredients' ])->where([ 'status' => Status::ACTIVE ]);
        }

        public function extras() : HasMany
        {
            return $this->hasMany(ItemExtra::class)->where([ 'status' => Status::ACTIVE ]);
        }

        public function ingredients() : BelongsToMany
        {
            return $this->belongsToMany(Ingredient::class , 'item_ingredients' , 'product_id' , 'ingredient_id')->withPivot([ 'quantity' , 'buying_price' , 'total' ]);
        }

        public function rawMaterials() : BelongsToMany
        {
            return $this->belongsToMany(Ingredient::class , 'item_raw_materials' , 'product_id' , 'ingredient_id')->withPivot([ 'quantity' , 'buying_price' , 'total','setup_id' ]);
        }

        public function addons() : HasMany
        {
            return $this->hasMany(ItemAddon::class);
        }

        public function category() : BelongsTo
        {
            return $this->belongsTo(ItemCategory::class , 'item_category_id' , 'id');
        }

        public function tax() : BelongsTo
        {
            return $this->belongsTo(Tax::class);
        }

        public function orders() : HasMany
        {
            return $this->hasMany(OrderItem::class , 'product_id' , 'id');
        }

        public function offer() : BelongsToMany
        {
            return $this->belongsToMany(Offer::class , 'offer_items');
        }
    }
