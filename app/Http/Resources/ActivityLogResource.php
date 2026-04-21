<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\BookingActivityLog;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin BookingActivityLog */
    class ActivityLogResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'user'       => [ 'id' => $this->user->id , 'name' => $this->user->name ] ,
                'action'     => $this->note ,
                'time'       => AppLibrary::time( $this->created_at ) ,
                'date'       => AppLibrary::date( $this->created_at ) ,
                'created_at' => datetime($this->created_at) ,
            ];
        }
    }
