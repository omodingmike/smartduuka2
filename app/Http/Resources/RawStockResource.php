<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Warehouse;
    use Illuminate\Http\Resources\Json\JsonResource;


    class RawStockResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $status = $this->status;
            return [
                'id'                       => $this->id ,
                'product_id'               => $this->product_id ,
                'driver'                   => $this->driver ,
                'number_plate'             => $this->number_plate ,
                'model_type'               => $this->model_type ,
                'currency'                 => currencySymbol() ,
                'products'                 => ProductAdminResource::collection( $this->products ) ,
                'location'                 => new WarehouseResource( Warehouse::find( $this->warehouse_id ) ) ,
                'from'                     => new WarehouseResource( Warehouse::find( $this->source_warehouse_id ) ) ,
                'to'                       => new WarehouseResource( Warehouse::find( $this->destination_warehouse_id ) ) ,
                'model_id'                 => $this->model_id ,
                'item_type'                => $this->item_type ,
                'item_id'                  => $this->item_id ,
                'variation_names'          => $this->variation_names ,
                'sku'                      => $this->sku ,
                'price'                    => $this->price ,
                'stock_type'               => $this->price ,
                'quantity'                 => abs( $this->quantity ) ,
                'quantity_text'            => number_format( abs( $this->quantity ) ) ,
                'discount'                 => $this->discount ,
                'subtotal'                 => $this->subtotal ,
                'total'                    => AppLibrary::currencyAmountFormat( $this->total ) ,
                'created_at'               => AppLibrary::datetime2( $this->created_at ) ,
                'tax'                      => $this->tax ,
                'status'                   => [
                    'value' => $status->value ,
                    'label' => $status->label() ,
                ] ,
                'type'                     => $this->type ,
                'updated_at'               => $this->updated_at ,
                'other_quantity'           => $this->other_quantity ,
                'warehouse_id'             => $this->warehouse_id ,
                'source_warehouse_id'      => $this->source_warehouse_id ,
                'destination_warehouse_id' => $this->destination_warehouse_id ,
                'description'              => $this->description ,
                'delivery'                 => $this->delivery ,
                'unit_id'                  => $this->unit_id ,
                'rate'                     => $this->rate ,
                'expiry_date'              => $this->expiry_date ,
                'purchase_quantity'        => $this->purchase_quantity ,
                'fractional_quantity'      => $this->fractional_quantity ,
                'reference'                => $this->reference ,
                'batch'                    => $this->batch ,
                'system_stock'             => $this->system_stock ,
                'physical_stock'           => $this->physical_stock ,
                'difference'               => $this->difference ,
                'discrepancy'              => $this->discrepancy ,
                'classification'           => $this->classification ,
                'creator'                  => $this->creator ,
                'variation_id'             => $this->variation_id ,
                'user_id'                  => $this->user_id ,
                'distribution_status'      => $this->distribution_status ,
                'sold'                     => $this->sold ,
                'returned'                 => $this->returned ,
            ];
        }
    }
