<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Stock;
    use App\Models\StockPurchaseRequest;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin StockPurchaseRequest */
    class StockPurchaseRequestResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $total = $this->stocks->sum( 'total' );
            return [
                'id'             => $this->id ,
                'reference'      => $this->reference ,
                'requester_name' => $this->requester_name ,
                'department'     => $this->department ,
                'priority'       => $this->priority ,
                'date'           => $this->date ,
                'date_formatted' => datetime( $this->date ) ,
                'reason'         => $this->reason ,
                'status'         => $this->status ,
                'total'          => $total ,
                'total_currency' => currency( $total ) ,
                'products'       => $this->stocks->map( function (Stock $stock) {
                    return [
                        'stock_id'         => $stock->id ,
                        'product_id'       => $stock->product_id ,
                        'product_name'     => $stock->product->name ,
                        'price'            => $stock->price ,
                        'quantity_ordered' => $stock->quantity_ordered ,
                        'currency_price'   => AppLibrary::currencyAmountFormat( $stock->price ) ,
                        'total'            => $stock->total ,
                        'total_currency'   => AppLibrary::currencyAmountFormat( $stock->total ) ,
                        'quantity'         => $stock->quantity ,
                        'unit'             => $stock->product->unit->short_name ,
                    ];
                } ) ,
            ];
        }
    }
