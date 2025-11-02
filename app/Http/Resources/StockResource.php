<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use App\Models\Warehouse;
    use Illuminate\Http\Resources\Json\JsonResource;

    class StockResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $product                 = Product::find($this['product_id']);
            $base_units_per_top_unit = $product?->base_units_per_top_unit;
            $units_per_mid_unit      = $product?->units_per_mid_unit;
            $retail_unit_id          = $product?->retail_unit_id;
            $stock                   = $base_units_per_top_unit ? intdiv($this['stock'] , $base_units_per_top_unit) : (int) $this['stock'];

            return [
                'product_id'      => $this['product_id'] ,
                'product_name'    => $this['product_name'] ,
                'variation_names' => $this['variation_names'] ,
                'status'          => $this['status'] ,
                'physical_stock'  => $this->when(enabledWarehouse() && isset($this['physical_stock']), fn () => $this['physical_stock']),
                'discrepancy'     => $this->when(enabledWarehouse() && isset($this['discrepancy']), fn () => $this['discrepancy']),
                'difference'      => $this->when(enabledWarehouse() && isset($this['difference']), fn () => $this['difference']),
                'system_stock'    => $this->when(enabledWarehouse() && isset($this['system_stock']), fn () => $this['system_stock']),
                'classification'  => $this->when(enabledWarehouse() && isset($this['classification']), fn () => $this['classification']),
                'creator'         => $this->when(enabledWarehouse() && isset($this['creator']), fn () => $this['creator']),
                'stock'           => number_format($stock) ,
                'mid_stock'       => $units_per_mid_unit ? number_format(intdiv($this['stock'] , $units_per_mid_unit)) : null ,
                'base_stock'      => number_format((int) $this['stock']) ,
                'location'        => new WarehouseResource(Warehouse::find($this['warehouse_id'])) ,
                'from'            => new WarehouseResource(Warehouse::find($this['source_warehouse_id'])) ,
                'to'              => new WarehouseResource(Warehouse::find($this['destination_warehouse_id'])) ,
                'stock_original'  => (int) $this['stock'] ,
                'delivery'        => AppLibrary::currencyAmountFormat($this['delivery']) ,
                'total'           => AppLibrary::currencyAmountFormat($this['total']) ,
                'other_stock'     => $this['other_stock'] ,
                'created_at'      => AppLibrary::datetime2($this['created_at']) ,
                'unit'            => $this['unit'] ,
                'weight'          => $this['weight'] ,
                'batch'           => $this['batch'] ,
                'reference'       => $this['reference'] ,
                'description'     => $this['description'] ,
                'mid_unit'        => $units_per_mid_unit ? ( Unit::find($product->mid_unit_id) )->name_code : null ,
                'base_unit'       => $retail_unit_id ? ( Unit::find($product->retail_unit_id) )->name_code : null ,
                'other_unit'      => $this['other_unit'] ,
                'units_nature'    => $this['units_nature']
            ];
        }
    }
