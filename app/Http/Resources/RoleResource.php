<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class RoleResource extends JsonResource
    {
        public function toArray($request) : array
        {
            return [
                "id"          => $this->id ,
                "name"        => $this->name ,
//                'users_count' => $this->whenCounted('users'),
                "guard"       => $this->guard_name ,
                'users_count' => $this->users_count
            ];
        }
    }
