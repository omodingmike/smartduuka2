<?php

    namespace App\Http\Resources;

    use App\Models\CommissionTarget;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CommissionTarget */
    class CommissionTargetResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'created_at' => $this->created_at ,
                'updated_at' => $this->updated_at ,

                'commission_id'        => $this->commission_id ,
                'user_id'              => $this->user_id ,
                'role_id'              => $this->role_id ,
                'product_id'           => $this->product_id ,
                'product_variation_id' => $this->product_variation_id ,

                'commission' => new CommissionResource( $this->whenLoaded( 'commission' ) ) ,
            ];
        }
    }
