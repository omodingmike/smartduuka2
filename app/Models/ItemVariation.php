<?php

    namespace App\Models;

    use App\Enums\Status;
    use App\Libraries\AppLibrary;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class ItemVariation extends Model
    {
        use HasFactory , SoftDeletes;

        protected $table   = "item_variations";
        protected $appends = [ 'convert_price' , 'currency_price' , 'flat_price', 'over_all_cost_currency' ];

        protected $fillable = [
            'product_id' ,
            'item_attribute_id' ,
            'name' ,
            'price' ,
            'caution' ,
            'status' ,
            'overall_cost'
        ];
        protected $casts    = [
            'id'                => 'integer' ,
            'product_id'           => 'integer' ,
            'item_attribute_id' => 'integer' ,
            'name'              => 'string' ,
            'price'             => 'decimal:6' ,
            'caution'           => 'string' ,
            'status'            => Status::class ,
        ];

        public function item()
        {
            return $this->belongsTo(Item::class);
        }

        public function getOverAllCostCurrencyAttribute()
        {
            return AppLibrary::currencyAmountFormat($this->overall_cost);
        }

        public function itemAttribute()
        {
            return $this->belongsTo(ItemAttribute::class);
        }

        public function getCurrencyPriceAttribute() : string
        {
            return AppLibrary::currencyAmountFormat($this->price);
        }

        public function getFlatPriceAttribute() : string
        {
            return AppLibrary::currencyAmountFormat($this->price);
        }

        public function getConvertPriceAttribute() : float
        {
            return AppLibrary::convertAmountFormat($this->price);
        }

        public function ingredients() : BelongsToMany
        {
            return $this->belongsToMany(Ingredient::class , 'variation_ingredients' , 'variation_id' , 'ingredient_id')->withPivot('quantity' , 'buying_price' , 'total' , 'created_at' , 'updated_at');
        }
    }
