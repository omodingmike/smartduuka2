<?php

    namespace App\Http\Resources;

    use App\Models\ExpenseCategory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ExpenseCategoryResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         * @mixin ExpenseCategory
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            $expenses = $this->expenses;
            return [
                'id'              => $this->id ,
                'name'            => $this->name ,
                'description'     => $this->description ,
                'parent_id'       => $this->parent_id ,
                'status'          => $this->status ,
                'expenseCount'    => number_format( $expenses->count() ) ,
                'totalSpent'      => currency( $expenses->sum( 'amount' ) ) ,
                'depth'           => $this->depth ,
                'path'            => $this->path ,
                'parent_category' => new ExpenseCategoryResource( $this->whenLoaded( 'parent_category' ) ) ,
            ];
        }
    }
