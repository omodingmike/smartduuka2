<?php

    namespace App\Http\Resources;


    use App\Enums\Status;
    use Illuminate\Http\Resources\Json\JsonResource;

    class DiningTableResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                "id"             => $this->id ,
                "name"           => $this->name ,
                "slug"           => $this->slug ,
                "size"           => $this->size ,
                "qr_code"        => asset($this->qr_code) ,
                "branch_id"      => $this->branch_id ,
                "branch_name"    => optional($this->branch)->name ,
                "status"         => $this->status ,
                "image"          => $this->image($this->status) ,
                "qr"             => $this->qr ,
                "branch_address" => $this->branch->address ,
                "branch_phone"   => $this->branch->phone ,
            ];
        }

        private function image(int $status) : string
        {
            switch ( $status ) {
                case Status::ACTIVE:
                case  Status::AVAILABLE:
                    return asset('images/default/table_available.png');

                case Status::RESERVED:
                    return asset('images/default/table_reserved.png');

                case Status::BILL_PRINTED:
                    return asset('images/default/table_bill_printed.png');

                default:
                    return asset('images/default/table_occupied.png');
            }
        }
    }
