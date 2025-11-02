<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use App\Models\Warehouse;
    use Illuminate\Http\Resources\Json\JsonResource;

    class StockTransferResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         * @return array
         */

        public function toArray($request)
        {
//            $product                 = Product::find($this['product_id']);
            $product                 = Product::find($this['product']['id']);
            $base_units_per_top_unit = $product->base_units_per_top_unit;
            $units_per_mid_unit      = $product->units_per_mid_unit;
            $retail_unit_id          = $product->retail_unit_id;

            return [
                'product_id'     => $product->id ,
                'product'        => $product->name ,
                'status'         => $this['status'] ,
                'stock'          => $base_units_per_top_unit ? intdiv(abs($this['quantity']) , $base_units_per_top_unit) : (int) abs($this['quantity']) ,
                'mid_stock'      => $units_per_mid_unit ? intdiv(abs($this['quantity']) , $units_per_mid_unit) : null ,
                'base_stock'     => (int) abs($this['quantity']) ,
                'location'       => new WarehouseResource(Warehouse::find($this['warehouse_id'])) ,
                'from'           => new WarehouseResource(Warehouse::find($this['source_warehouse_id'])) ,
                'to'             => new WarehouseResource(Warehouse::find($this['destination_warehouse_id'])) ,
                'stock_original' => (int) abs($this['quantity']) ,
                'delivery'       => AppLibrary::currencyAmountFormat($this['delivery']) ,
                'total'          => AppLibrary::currencyAmountFormat($this['total']) ,
                'other_stock'    => $this['other_stock'] ,
                'created_at'     => AppLibrary::datetime2($this['created_at']) ,
                'unit'           => $this['product']['unit'] ,
                'batch'          => $this['batch'] ,
                'reference'      => $this['reference'] ,
                'description'    => $this['description'] ,
                'mid_unit'       => $units_per_mid_unit ? ( Unit::find($product->mid_unit_id) )->name_code : null ,
                'base_unit'      => $retail_unit_id ? ( Unit::find($product->retail_unit_id) )->name_code : null ,
                'other_unit'     => $this['product']['other_unit'] ,
                'units_nature'   => $this['product']['units_nature']
            ];
        }
    }
