<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;

    class ProductionProcessResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => Str::padLeft($this->id , 5 , 0) ,
                'setup'               => [
                    'id' => Str::padLeft($this->setup->id , 5 , 0),
                    'name' => $this->setup->name ,
                ] ,
                'quantity'            => $this->quantity ,
                'actual_quantity'     => $this->actual_quantity ,
                'status'              => $this->status ,
                'damage_type'         => $this->damage_type ,
                'damage_reason'       => $this->damage_reason ,
                'damage_result'       => $this->damage_result ,
                'start_date'          => AppLibrary::date($this->start_date) ,
                'schedule_start_date' => $this->schedule_start_date ? AppLibrary::date($this->schedule_start_date) : 'NA' ,
                'end_date'            => $this->end_date ? AppLibrary::date($this->end_date) : 'NA' ,
            ];
        }
    }
