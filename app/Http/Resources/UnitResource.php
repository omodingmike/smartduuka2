<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class UnitResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'id'                => $this->id ,
                'name'              => $this->name ,
                'short_name'        => $this->short_name ,
                'conversion_factor' => $this->conversion_factor ,
                'status'            => $this->status ,
                'label'             => $this->label ,
                'base_unit_id'      => $this->base_unit_id ,
            ];
        }
    }
