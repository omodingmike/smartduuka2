<?php

    namespace App\Models;

    use App\Enums\EnumDistributionStockStatusEnum;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    class Stock extends Model
    {
        use HasFactory;
        use HasRecursiveRelationships;

        protected $table   = "stocks";
        protected $guarded = [];

        protected $casts = [
            'product_id'          => 'integer' ,
            'item_id'             => 'integer' ,
            'model_type'          => 'string' ,
            'model_id'            => 'integer' ,
            'item_type'           => 'string' ,
            'variation_names'     => 'string' ,
            'price'               => 'decimal:6' ,
            'quantity'            => 'decimal:2' ,
            'discount'            => 'decimal:6' ,
            'tax'                 => 'decimal:6' ,
            'sku'                 => 'string' ,
            'status'              => 'integer' ,
            'subtotal'            => 'decimal:6' ,
            'total'               => 'decimal:6' ,
            'rate'                => 'decimal:6' ,
            'purchase_quantity'   => 'integer' ,
            'expiry_date'         => 'datetime' ,
            'distribution_status' => EnumDistributionStockStatusEnum::class ,
        ];

        public function item() : MorphTo
        {
            return $this->morphTo();
        }

        public function warehouse() : BelongsTo
        {
            return $this->belongsTo( Warehouse::class , 'warehouse_id' , 'id' );
        }

        public function unit() : BelongsTo
        {
            return $this->belongsTo( Unit::class , 'unit_id' , 'id' );
        }

        public function user() : BelongsTo
        {
            return $this->belongsTo( User::class , 'creator' , 'id' );
        }

        public function distributor() : BelongsTo
        {
            return $this->belongsTo( User::class , 'user_id' , 'id' );
        }

        public function otherWarehouse() : BelongsTo
        {
            return $this->belongsTo( Warehouse::class , 'other_warehouse_id' , 'id' );
        }

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class )->withTrashed();
        }

        public function products() : Stock | Builder | HasMany
        {
            return $this->hasMany( Product::class , 'id' , 'product_id' )->withTrashed();
        }

        public function ingredient() : BelongsTo
        {
            return $this->belongsTo( Ingredient::class , 'product_id' , 'id' )->withTrashed();
        }

        public function productTax() : BelongsTo
        {
            return $this->belongsTo( Tax::class , 'tax_id' );
        }

        public function stockTaxes() : HasMany
        {
            return $this->hasMany( StockTax::class );
        }

    }
