<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Product;
    use App\Models\Unit;
    use App\Models\Warehouse;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;


    class StockResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $product                 = Product::find( $this[ 'product_id' ] );
            $base_units_per_top_unit = $product?->base_units_per_top_unit;
            $units_per_mid_unit      = $product?->units_per_mid_unit;
            $retail_unit_id          = $product?->retail_unit_id;

            $isNumeric = is_numeric( $this[ 'stock' ] );
            $rawStock  = $isNumeric ? (float) $this[ 'stock' ] : 0;

            $stock          = ( $isNumeric && $base_units_per_top_unit ) ? intdiv( $rawStock , $base_units_per_top_unit ) : (int) $rawStock;
            $physical_stock = (float) ( $this[ 'physical_stock' ] ?? 0 );
            $system_stock   = (float) ( $this[ 'system_stock' ] ?? 0 );
            $difference     = $physical_stock - $system_stock;
            $discrepancy    = match ( TRUE ) {
                $difference < 0 => 'Shortage' ,
                $difference > 0 => 'Excess' ,
                default         => 'Match' ,
            };

            $expiryDate = $this[ 'expiry_date' ] ?? NULL;
            $daysLeft   = $expiryDate ? (int) now()->diffInDays( Carbon::parse( $expiryDate ) , FALSE ) : NULL;

            return [
                'key'                        => Str::uuid()->getHex() ,
                'product_id'                 => $this[ 'product_id' ] ,
                'products'                   => $this[ 'products' ] ? SimpleProductResource::collection( $this[ 'products' ] ) : [] ,
                'product_name'               => $this[ 'product_name' ] ,
                'category'                   => $this[ 'category' ] ,
                'variation_names'            => $this[ 'variation_names' ] ,
                'variation_id'               => $this[ 'variation_id' ] ?? NULL ,
                'status'                     => $this[ 'status' ] ,
                'stock_status'               => $this[ 'stock_status' ] ,
                'discrepancy'                => $discrepancy ,
                'physical_stock'             => $this->when( enabledWarehouse() && isset( $this[ 'physical_stock' ] ) , fn() => $this[ 'physical_stock' ] ) ,
                'difference'                 => $this->when( enabledWarehouse() && isset( $this[ 'difference' ] ) , fn() => $this[ 'difference' ] ) ,
                'system_stock'               => $this->when( enabledWarehouse() && isset( $this[ 'system_stock' ] ) , fn() => $this[ 'system_stock' ] ) ,
                'classification'             => $this->when( enabledWarehouse() && isset( $this[ 'classification' ] ) , fn() => $this[ 'classification' ] ) ,
                'creator'                    => $this->when( enabledWarehouse() && isset( $this[ 'creator' ] ) , fn() => $this[ 'creator' ] ) ,
                'stock'                      => $isNumeric ? number_format( $stock ) : $this[ 'stock' ] ,
                'stock_value'                => $stock ,
                'quantity_received'          => $this[ 'quantity_received' ] ,
                'quantity_deposited'         => $this[ 'quantity_deposited' ] ,
                'mid_stock'                  => ( $isNumeric && $units_per_mid_unit ) ? number_format( intdiv( $rawStock , $units_per_mid_unit ) ) : NULL ,
                'base_stock'                 => $isNumeric ? number_format( (int) $rawStock ) : $this[ 'stock' ] ,
                'location'                   => new SimpleWarehouseResource( Warehouse::find( $this[ 'warehouse_id' ] ) ) ,
                'from'                       => new SimpleWarehouseResource( Warehouse::find( $this[ 'source_warehouse_id' ] ?? NULL ) ) ,
                'to'                         => new SimpleWarehouseResource( Warehouse::find( $this[ 'destination_warehouse_id' ] ?? NULL ) ) ,
                'stock_original'             => $this[ 'stock' ] ,
                'delivery'                   => AppLibrary::currencyAmountFormat( $this[ 'delivery' ] ?? 0 ) ,
                'total'                      => AppLibrary::currencyAmountFormat( $this[ 'total' ] ?? 0 ) ,
                'other_stock'                => $this[ 'other_stock' ] ,
                'created_at'                 => AppLibrary::datetime2( $this[ 'created_at' ] ) ,
                'unit'                       => $this[ 'unit' ] ,
                'weight'                     => $this[ 'weight' ] ,
                'batch'                      => $this[ 'batch' ] ,
                'reference'                  => $this[ 'reference' ] ,
                'description'                => $this[ 'description' ] ,
                'mid_unit'                   => $units_per_mid_unit ? ( Unit::find( $product->mid_unit_id ) )?->name_code : NULL ,
                'base_unit'                  => $retail_unit_id ? ( Unit::find( $product->retail_unit_id ) )?->name_code : NULL ,
                'other_unit'                 => $this[ 'other_unit' ] ,
                'units_nature'               => $this[ 'units_nature' ] ,
                'unit_price'                 => AppLibrary::currencyAmountFormat( $this[ 'unit_price' ] ?? 0 ) ,
                'total_price'                => AppLibrary::currencyAmountFormat( $this[ 'total_price' ] ?? 0 ) ,
                'low_stock_quantity_warning' => $product?->low_stock_quantity_warning ?? 0 ,
//                'expiry_date'     => $expiryDate ? Carbon::parse($expiryDate)->format('Y-m-d') : null,
                'expiry_date'                => $expiryDate ? AppLibrary::date( $expiryDate ) : NULL ,
                'days_left'                  => $daysLeft ,
            ];
        }

        public function toArray1($request) : array
        {
            $product                 = Product::find( $this[ 'product_id' ] );
            $base_units_per_top_unit = $product?->base_units_per_top_unit;
            $units_per_mid_unit      = $product?->units_per_mid_unit;
            $retail_unit_id          = $product?->retail_unit_id;
            $stock                   = $base_units_per_top_unit ? intdiv( $this[ 'stock' ] , $base_units_per_top_unit ) : (int) $this[ 'stock' ];
            $physical_stock          = (float) $this[ 'physical_stock' ];
            $system_stock            = (float) $this[ 'system_stock' ];
            $difference              = $physical_stock - $system_stock;
            $discrepancy             = match ( TRUE ) {
                $difference < 0 => 'Shortage' ,
                $difference > 0 => 'Excess' ,
                default         => 'Match' ,
            };
            $rawStock                = $this[ 'stock' ] === 'N/C' ? 0 : (float) $this[ 'stock' ];

            return [
                'key'             => Str::uuid()->getHex() ,
                'product_id'      => $this[ 'product_id' ] ,
                'products'        => $this[ 'products' ] ? SimpleProductResource::collection( $this[ 'products' ] ) : [] ,
//                'products'        =>  $this[ 'products' ],
                'product_name'    => $this[ 'product_name' ] ,
                'variation_names' => $this[ 'variation_names' ] ,
                'variation_id'    => $this[ 'variation_id' ] ?? NULL ,
                'status'          => $this[ 'status' ] ,
                'stock_status'    => $this[ 'stock_status' ] ,
                'discrepancy'     => $discrepancy ,
                'physical_stock'  => $this->when( enabledWarehouse() && isset( $this[ 'physical_stock' ] ) , fn() => $this[ 'physical_stock' ] ) ,
                'difference'      => $this->when( enabledWarehouse() && isset( $this[ 'difference' ] ) , fn() => $this[ 'difference' ] ) ,
                'system_stock'    => $this->when( enabledWarehouse() && isset( $this[ 'system_stock' ] ) , fn() => $this[ 'system_stock' ] ) ,
                'classification'  => $this->when( enabledWarehouse() && isset( $this[ 'classification' ] ) , fn() => $this[ 'classification' ] ) ,
                'creator'         => $this->when( enabledWarehouse() && isset( $this[ 'creator' ] ) , fn() => $this[ 'creator' ] ) ,
                'stock'           => number_format( $rawStock ) ,
                'stock_value'     => $stock ,
                'mid_stock'       => $units_per_mid_unit ? number_format( intdiv( $this[ 'stock' ] , $units_per_mid_unit ) ) : NULL ,
                'base_stock'      => number_format( (int) $this[ 'stock' ] ) ,
                'location'        => new SimpleWarehouseResource( Warehouse::find( $this[ 'warehouse_id' ] ) ) ,
                'from'            => new SimpleWarehouseResource( Warehouse::find( $this[ 'source_warehouse_id' ] ) ) ,
                'to'              => new SimpleWarehouseResource( Warehouse::find( $this[ 'destination_warehouse_id' ] ) ) ,
                'stock_original'  => (int) $this[ 'stock' ] ,
                'delivery'        => AppLibrary::currencyAmountFormat( $this[ 'delivery' ] ) ,
                'total'           => AppLibrary::currencyAmountFormat( $this[ 'total' ] ) ,
                'other_stock'     => $this[ 'other_stock' ] ,
                'created_at'      => AppLibrary::datetime2( $this[ 'created_at' ] ) ,
                'unit'            => $this[ 'unit' ] ,
                'weight'          => $this[ 'weight' ] ,
                'batch'           => $this[ 'batch' ] ,
                'reference'       => $this[ 'reference' ] ,
                'description'     => $this[ 'description' ] ,
                'mid_unit'        => $units_per_mid_unit ? ( Unit::find( $product->mid_unit_id ) )->name_code : NULL ,
                'base_unit'       => $retail_unit_id ? ( Unit::find( $product->retail_unit_id ) )->name_code : NULL ,
                'other_unit'      => $this[ 'other_unit' ] ,
                'units_nature'    => $this[ 'units_nature' ]
            ];
        }
    }
