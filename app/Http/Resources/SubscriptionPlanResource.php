<?php

    namespace App\Http\Resources;

    use App\Models\SubscriptionPlan;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin SubscriptionPlan */
    class SubscriptionPlanResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'description' => $this->description ,
                'features'    => $this->features ,
                'base_amount' => $this->base_amount ,
                'popular'     => $this->popular ,
            ];
        }
    }
