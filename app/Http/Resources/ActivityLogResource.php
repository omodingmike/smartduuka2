<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ActivityLogResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
//                'user'       => $this->user_id ,
                'user'       => $this->user ,
                'action'     => $this->action ,
                'created_at' =>Carbon::parse($this->created_at)->format('d-m-Y H:i:s')  ,
                'updated_at' => $this->updated_at ,
            ];
        }
    }
