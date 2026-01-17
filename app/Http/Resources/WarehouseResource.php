<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class WarehouseResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'           => $this->id ,
                'name'         => $this->name ,
                'email'        => $this->email ,
                'location'     => $this->location ,
//                'default_warehouse_id' => Warehouse::first()->id ,
//                'deletable'            => $this->deletable ,
                'phone'        => $this->phone ,
                'manager'      => $this->manager ,
                'capacity'     => $this->capacity ,
                'status'       => $this->status ,
                'status_label' => statusLabel( $this->status ) ,
            ];
        }
    }
