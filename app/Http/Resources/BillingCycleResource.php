<?php

    namespace App\Http\Resources;

    use App\Models\BillingCycle;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin BillingCycle */
    class BillingCycleResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'name'       => $this->name ,
                'multiplier' => $this->multiplier ,
                'discount'   => $this->discount ,
                'badge'      => $this->when( $this->discount > 0 , ( -$this->discount * 100 ) . '%' ) ,
            ];
        }
    }
