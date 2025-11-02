<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SpecialDaysResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'status'      => $this->status ,
                'description' => $this->description ,
                'date'        => AppLibrary::date($this->date) ,
                'timestamp'   => $this->date->timestamp ,
            ];
        }
    }
