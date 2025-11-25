<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\CleaningOrderItem;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CleaningOrderItem */
    class CleaningOrderItemResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => $this->id ,
                'description'         => $this->description ,
                'quantity'            => $this->quantity ,
                'notes'               => $this->notes ,
                'total'               => AppLibrary::currencyAmountFormat( ( $this->quantity * $this->cleaningService->price ) ) ,
                'created_at'          => $this->created_at ,
                'updated_at'          => $this->updated_at ,
                'cleaning_service_id' => $this->cleaning_service_id ,
                'cleaningService'     => new CleaningServiceResource( $this->whenLoaded( 'cleaningService' ) ) ,
            ];
        }
    }
