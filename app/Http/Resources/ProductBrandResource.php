<?php

    namespace App\Http\Resources;


    use App\Enums\Ask;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ProductBrandResource extends JsonResource
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
                'id'           => $this->id ,
                'name'         => $this->name ,
                'products'     => $this->products->count() ,
                'slug'         => $this->slug ,
                'description'  => $this->description === NULL ? '' : $this->description ,
                'status'       => $this->status ?? Ask::NO ,
                'status_label' => statusLabel( $this->status ) ,
                'thumb'        => $this->thumb ,
                'cover'        => $this->cover
            ];
        }
    }
