<?php

    namespace App\Http\Resources;

    use App\Models\BookingActivityLog;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin BookingActivityLog */
    class BookingActivityLogResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'status'     => $this->status ,
                'note'       => $this->note ,
                'created_at' => $this->created_at ,
                'updated_at' => $this->updated_at ,

                'user_id'    => $this->user_id ,
                'booking_id' => $this->booking_id ,

                'user'    => new UserResource( $this->whenLoaded( 'user' ) ) ,
                'booking' => new BookingResource( $this->whenLoaded( 'booking' ) ) ,
            ];
        }
    }
