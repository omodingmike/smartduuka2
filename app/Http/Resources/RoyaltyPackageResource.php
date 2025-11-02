<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class RoyaltyPackageResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'name'           => $this->name ,
                'status'         => $this->status ,
                'minimum_points' => $this->minimum_points ,
                'description'    => $this->description ,
                'benefits'       => $this->benefits->map(function ($benefit) {
                    return [
                        'id'          => $benefit->id ,
                        'name'        => $benefit->name ,
                        'description' => $benefit->description ,
                        'status'      => $benefit->status ,
                    ];
                }) ,
            ];
        }
    }
