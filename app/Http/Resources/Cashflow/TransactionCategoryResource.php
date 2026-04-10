<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\TransactionCategory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin TransactionCategory */
    class TransactionCategoryResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'          => $this->id ,
                'name'        => $this->name ,
                'cash_type'   => $this->cash_type ,
                'usage'       => $this->usage ,
                'totalVolume' => currency( $this->totalVolume ) ,
            ];
        }
    }
