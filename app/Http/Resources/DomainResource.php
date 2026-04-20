<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Stancl\Tenancy\Database\Models\Domain;

    /**
     * @mixin Domain
     */
    class DomainResource extends JsonResource
    {

        public function toArray(Request $request) : array
        {
            return [
                'domain'     => $this->domain ,
                'id'         => $this->id ,
                'created_at' => datetime( $this->created_at ) ,
            ];
        }
    }
