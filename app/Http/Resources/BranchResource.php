<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class BranchResource extends JsonResource
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
                "id"         => $this->id ,
                "name"       => $this->name ,
                "email"      => $this->email === NULL ? '' : $this->email ,
                "phone"      => $this->phone === NULL ? '' : $this->phone ,
                "location"   => $this->address ,
                "staffCount" => 0 ,
                "code"       => $this->code ,
                "manager"    => $this->manager ,
                "status"     => $this->status
            ];
        }
    }
