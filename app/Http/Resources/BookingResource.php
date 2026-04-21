<?php

    namespace App\Http\Resources;

    use App\Models\Booking;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Booking */
    class BookingResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'customer_name'  => $this->customer_name ,
                'customer_phone' => $this->customer_phone ,
                'date'           => datetime( $this->date ) ,
                'status'         => $this->status ,
                'total'          => $this->total ,
                'bookingId'      => $this->bookingId ,
                'total_currency' => currency( $this->total ) ,
                'notes'          => $this->notes ,
                'service_id'     => $this->service_id ,
                'service'        => new ServiceResource( $this->whenLoaded( 'service' ) ) ,
                'adds_on'        => BookingAddOnResource::collection( $this->whenLoaded( 'addsOn' ) ) ,
                'activity_logs'  => ActivityLogResource::collection( $this->whenLoaded( 'activityLogs' ) ) ,
            ];
        }
    }
