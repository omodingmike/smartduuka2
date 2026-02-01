<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'purchase_id' => $this->purchase_id,
            'date' => $this->date,
            'converted_date' => AppLibrary::datetime2($this->date),
            'debit_note' => $this->debit_note,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supplier' => new SimpleSupplierResource($this->whenLoaded('supplier')),
            'purchase' => new PurchaseResource($this->whenLoaded('purchase')),
        ];
    }
}
