<?php

    namespace App\Http\Resources;

    use App\Models\Warehouse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductStockResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            return [
                'id'                       => $this->id ,
                'product_id'               => $this->product_id ,
                'model_type'               => $this->model_type ,
                'model_id'                 => $this->model_id ,
                'item_type'                => $this->item_type ,
                'stock'                    => $this->stock ,
                'item_id'                  => $this->item_id ,
                'location'                 => new WarehouseResource( Warehouse::find( $this[ 'warehouse_id' ] ) ) ,
                'from'                     => new WarehouseResource( Warehouse::find( $this[ 'source_warehouse_id' ] ) ) ,
                'to'                       => new WarehouseResource( Warehouse::find( $this[ 'destination_warehouse_id' ] ) ) ,
                'variation_names'          => $this->variation_names ,
                'sku'                      => $this->sku ,
                'price'                    => $this->price ,
                'quantity'                 => $this->quantity ,
                'discount'                 => $this->discount ,
                'subtotal'                 => $this->subtotal ,
                'total'                    => $this->total ,
                'tax'                      => $this->tax ,
                'status'                   => [
                    'value' => $this->status->value ,
                    'label' => $this->status->label() ,
                ] ,
                'type'                     => $this->type ,
                'created_at'               => $this->created_at ,
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
                'products'                 => StockProductResource::collection( $this->whenLoaded( 'stockProducts' ) ) ,
            ];
        }
    }
