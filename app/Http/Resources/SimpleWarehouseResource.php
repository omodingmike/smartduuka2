<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SimpleWarehouseResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'           => $this->id ,
                'name'         => $this->name ,
                'email'        => $this->email ,
                'location'     => $this->location ,
                'phone'        => $this->phone ,
                'manager'      => $this->manager ,
                'capacity'     => $this->capacity ,
                'status'       => $this->status ,
                'status_label' => statusLabel( $this->status ) ,
            ];
        }
    }
