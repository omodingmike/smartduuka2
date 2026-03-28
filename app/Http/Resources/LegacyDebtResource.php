<?php

    namespace App\Http\Resources;

    use App\Models\LegacyDebt;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin LegacyDebt */
    class LegacyDebtResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'amount'     => $this->amount ,
                'date'       => $this->date ,
                'notes'      => $this->notes ,
                'created_at' => $this->created_at ,
                'updated_at' => $this->updated_at ,
                'user_id'    => $this->user_id ,
                'user'       => new UserResource( $this->whenLoaded( 'user' ) ) ,
            ];
        }
    }
