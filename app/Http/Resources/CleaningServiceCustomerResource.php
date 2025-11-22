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
                'id'         => $this->id ,
                'name'       => $this->name ,
                'phone'      => $this->phone ,
            ];
        }
    }
