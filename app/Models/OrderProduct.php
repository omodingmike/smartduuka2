<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;

    class OrderProduct extends Model
    {
        use HasFactory;

        public $timestamps = FALSE;

        protected $fillable = [
            'order_id' ,
            'item_id' ,
            'item_type' ,
            'quantity' ,
            'total' ,
            'unit_price' ,
            'quantity_picked' ,
            'product_attribute_id' ,
            'product_attribute_option_id' ,
            'variation_id' ,
            'product_id' ,
            'price_id' ,
            'price_type'
        ];

        public function order() : BelongsTo
        {
            return $this->belongsTo( Order::class );
        }

        public function item() : MorphTo
        {
            return $this->morphTo()->withTrashed();
        }

        public function product() : BelongsTo
        {
            return $this->belongsTo( Product::class )->withTrashed();
        }

        public function productAttribute() : BelongsTo
        {
            return $this->belongsTo( ProductAttribute::class )->withTrashed();
        }

        public function productAttributeOption() : BelongsTo
        {
            return $this->belongsTo( ProductAttributeOption::class )->withTrashed();
        }

        public function price() : MorphTo
        {
            return $this->morphTo();
        }

        public function totalCost()
        {
            $total = 0;
            if ( $this->item_type === Product::class ) {
                $product = Product::find( $this->item_id );
                if ( $product ) {
                    $total += $product->buying_price * $this->quantity;
                }
            }
            elseif ( $this->item_type === ProductVariation::class ) {
                $variation = ProductVariation::find( $this->item_id );
                if ( $variation ) {
                    $retailPrice = $variation->retailPrices()->first();
                    if ( $retailPrice ) {
                        $total += $retailPrice->buying_price * $this->quantity;
                    }
                }
            }
            return $total;
        }
    }
