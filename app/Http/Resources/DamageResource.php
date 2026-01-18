<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;


    class DamageResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $stock = $this->stocks->first();
            return [
                'id'                   => $this->id ,
                'reason'               => $this->reason ,
                'image'                => $this->image ,
                'quantity'             => abs( $stock->quantity ) ,
                'creator'              => $this->creator->name ,
                'date'                 => $this->date->format( 'd-m-Y' ) ,
                'stock'                => $stock ,
                'loss'                 => AppLibrary::currencyAmountFormat( abs( $stock->quantity * $stock->product->buying_price ) ) ,
                'converted_date'       => AppLibrary::datetime( $this->date ) ,
                'reference_no'         => $this->reference_no ,
                'total'                => $this->total ,
                'total_currency_price' => AppLibrary::currencyAmountFormat( $this->total ) ,
                'total_flat_price'     => AppLibrary::flatAmountFormat( $this->total ) ,
                'note'                 => $this->note ,
                'status'               => [
                    'value' => $this->status?->value ,
                    'label' => $this->status?->label() ,
                ] ,
            ];
        }
    }
