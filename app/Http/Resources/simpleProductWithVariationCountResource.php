<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class simpleProductWithVariationCountResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $product                 = Product::find($this->id);
            $base_units_per_top_unit = $product->base_units_per_top_unit;
            $units_per_mid_unit      = $product->units_per_mid_unit;
            $price                   = count($this->variations) > 0 ? $this->variation_price : $this->selling_price;
            return [
                'id'                         => $this->id ,
                'name'                       => $this->name ,
                'buying_price'               => AppLibrary::convertAmountFormat($this->buying_price) ,
                'is_variation'               => count($this->variations) > 0 ? true : false ,
                'units_nature'               => $this->units_nature ,
                'other_unit_id'              => $this->otherUnits ,
                'prices'                     => $this->prices ,
                'unit'                       => $this->unit ,
                'sku'                        => $this->sku ,
                'taxes'                      => $this->productTaxes->pluck('tax_id') ,
                'retail_unit_id'             => $this->retail_unit_id ,
                'retail_unit'                => $this->retail_unit_id ? new UnitResource(Unit::find($this->retail_unit_id)) : null ,
                'mid_unit'                   => $this->retail_unit_id ? new UnitResource(Unit::find($this->mid_unit_id)) : null ,
                'top_unit'                   => $this->top_unit_id ? new UnitResource(Unit::find($this->top_unit_id)) : null ,
                'mid_unit_id'                => $product->mid_unit_id ,
                'top_unit_id'                => $product->top_unit_id ,
                'units_per_mid_unit'         => $product->units_per_mid_unit ,
                'mid_units_per_top_unit'     => $product->mid_units_per_top_unit ,
                'base_units_per_top_unit'    => $product->base_units_per_top_unit ,
                'mid_unit_wholesale_price'   => $product->mid_unit_wholesale_price ,
                'top_unit_wholesale_price'   => $product->top_unit_wholesale_price ,
                'retail_price_per_base_unit' => $product->retail_price_per_base_unit ,
                'unit_id'                    => $product->unit_id ,
                'selling_units'              => UnitResource::collection($this->sellingUnits) ,
            ];
        }
    }
