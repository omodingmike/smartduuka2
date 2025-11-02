<?php

    namespace App\Http\Resources;

    use App\Models\Warehouse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class WarehouseResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                   => $this->id ,
                'name'                 => $this->name ,
                'email'                => $this->email ,
                'location'             => $this->location ,
                'default_warehouse_id' => Warehouse::first()->id ,
                'deletable'            => $this->deletable ,
                'phone'                => $this->phone ,
                'country_code'         => $this->country_code
            ];
        }
    }
