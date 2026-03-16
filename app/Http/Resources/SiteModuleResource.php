<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SiteModuleResource extends JsonResource
    {
        /**
         * The "data" wrapper that should be applied.
         *
         * @var string|null
         */
        public static $wrap = 'data';

        /**
         * Transform the resource into an array.
         *
         * @param  Request  $request
         * @return array
         */
        public function toArray($request): array
        {
            // The controller now passes the module array directly.
            // We can just return it as is.
            // The resource will automatically be converted to JSON.
            return $this->resource;
        }
    }
