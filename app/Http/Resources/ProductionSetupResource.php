<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;


    class ProductionSetupResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'   => Str::padLeft($this['id'] , 5 , 0) ,
                'name' => $this['name'] ,
                'item' => $this['product'] ,
            ];
        }
    }
