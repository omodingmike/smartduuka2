<?php

    namespace App\Http\Resources\Cashflow;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ActivityLogResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'date'        => datetime( $this->created_at ) ,
                'subject'     => $this->subject ,
                'causer'      => $this->causer ,
                'id'          => $this->id ,
                'description' => $this->description ,
            ];
//            return parent::toArray( $request );
        }
    }
