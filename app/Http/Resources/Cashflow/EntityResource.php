<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\Entity;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Entity */
    class EntityResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'      => $this->id ,
                'name'    => $this->name ,
                'type'    => $this->type ,
                'cleared' => currency( $this->cleared_total ?? $this->cleared ) ,
                'balance' => currency( ( $this->cash_in_total ?? 0 ) - ( $this->cash_out_total ?? 0 ) ) ,
            ];
        }
    }
