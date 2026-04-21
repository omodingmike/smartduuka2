<?php

    namespace App\Http\Resources;

    use App\Models\BookingAddOn;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin BookingAddOn */
    class BookingAddOnResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                => $this->id ,
                'booking_id'        => $this->booking_id ,
                'service_add_on_id' => $this->service_add_on_id ,
                'booking'           => new BookingResource( $this->whenLoaded( 'booking' ) ) ,
                'serviceAddOn'      => new ServiceAddOnResource( $this->whenLoaded( 'serviceAddOn' ) ) ,
            ];
        }
    }
