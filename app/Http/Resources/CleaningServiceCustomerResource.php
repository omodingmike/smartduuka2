<?php

    namespace App\Http\Resources;

    use App\Models\CleaningServiceCustomer;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CleaningServiceCustomer */
    class CleaningServiceCustomerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'    => $this->id ?? NULL ,
                'name'  => $this->name ?? NULL ,
                'phone' => $this->phone ?? NULL ,
            ];
        }
    }
