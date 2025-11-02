<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class ItemVariationGroupByAttributeResource extends JsonResource
{
    public function toArray($request): array
    {
//        info($this->toArray($request));
        return [
            'item_attribute_id' => $this->item_attribute_id,
            'name'              => optional($this->item_attribute)->name,
            'children'          => ItemVariationResource::collection($this->children),
//            'overall_cost'      => ItemVariationResource::collection($this->overall_cost)
        ];
    }

}
