<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ChartOfAccountGroupResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                 => $this->id ,
                'name'               => $this->name ,
                'can_delete'         => $this->can_delete ,
                'type'               => $this->type ,
                'parent_id'          => $this->parent_id ,
                'nature'             => $this->nature ,
                'code'               => null ,
                'amount'             => null ,
                'children_recursive' => $this->childrenRecursive ,
                'ledgers'            => $this->ledgers ,
            ];
        }
    }
