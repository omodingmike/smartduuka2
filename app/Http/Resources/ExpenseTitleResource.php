<?php

    namespace App\Http\Resources;

    use App\Models\ExpenseTitle;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ExpenseTitle */
    class ExpenseTitleResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'   => $this->id ,
                'name' => $this->name ,
            ];
        }
    }
