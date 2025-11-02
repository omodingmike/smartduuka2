<?php

    namespace App\Http\Resources;


    use App\Enums\StockExpireStatus;
    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ExpiryStockResource extends JsonResource
    {
        public function toArray($request)
        {
            $days       = now()->diffInDays($this['expiry_date'] , false);
            $asset_path = ( $days <= 30 && $days > 0 ) ? asset('svg/danger_icon.svg') : ( $days > 30 ? asset('svg/ok_icon.svg') :
                asset('svg/expired_icon.svg') );
            $text       = ( $days <= 30 && $days > 0 ) ? 'Expiring Soon' : ( $days > 30 ? 'Ok' : 'Expired' );

            return [
                'product_name' => $this->product->name ,
                'expiry_date'  => AppLibrary::datetime2($this->expiry_date) ,
                'quantity' => number_format($this->quantity) . ' ' . $this->product->unit->code,
                'days_left'    => "$days days" ,
                'status'       => ( $days <= 30 && $days > 0 ) ? StockExpireStatus::SOON : ( $days > 30 ? StockExpireStatus::OKAY :
                    StockExpireStatus::EXPIRED ) ,
                'image'        => $asset_path ,
                'text'         => $text ,
                'location'     => $this->warehouse->name
            ];
        }
    }
