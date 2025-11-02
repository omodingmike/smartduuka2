<?php

namespace App\Http\Resources;


use App\Enums\Status;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\isNull;

class ItemIngredientResource extends JsonResource
{

    public $variation = 0;
    public function toArray($request)
    {
        return [
            'id'                             => $this->id,
            'name'                           => $this->name,
            'buying_price'                   => $this->buying_price,
            'unit'                           => $this->unit,
            'quantity'                       => $this->quantity,
            'quantity_alert'                 => $this->quantity_alert,
            'status'                         => $this->status,
            'pivot'                         => $this->pivot,
        ];
    }
}
