<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Stock;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Number;

    class PurchaseResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @return array<string, mixed>
         */
        public function toArray(Request $request) : array
        {
            return [
                'id'                   => $this->id ,
                'supplier_id'          => $this->supplier_id ,
                'date'                 => $this->date ,
                'converted_date'       => AppLibrary::datetime2( $this->date ) ,
                'reference_no'         => $this->reference_no ,
                'status'               => $this->status ,
                'payment_status'       => $this->payment_status ,
                'total'                => $this->total ,
                'total_words'          => ucwords( Number::spell( (int) $this->total ) ).' Shillings Only' ,
                'paid'                 => $this->paid ,
                'type'                 => $this->type ,
                'shipping'             => $this->shipping ,
                'balance'              => $this->balance ,
                'purchasePayments'     => PurchasePaymentResource::collection( $this->purchasePayments ) ,
                'paymentMethods'       => $this->purchasePayments
                    ->pluck( 'paymentMethod.name' )
                    ->unique()
                    ->implode( ', ' ) ,
                'products'             => $this->stocks->map( function (Stock $stock) {
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
                'creator'              => new UserResource ( $this->creator ) ,
                'paid_currency'        => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'shipping_currency'    => AppLibrary::currencyAmountFormat( $this->shipping ) ,
                'total_currency_price' => AppLibrary::currencyAmountFormat( $this->total ) ,
                'balance_currency'     => AppLibrary::currencyAmountFormat( AppLibrary::flatAmountFormat( $this->balance ) ) ,
                'total_flat_price'     => AppLibrary::flatAmountFormat( $this->total ) ,
                'notes'                => $this->notes ,
                'supplier'             => new SimpleSupplierResource( $this->supplier ) ,
                'stocks'               => StockResource::collection( $this->stocks ) ,
            ];
        }
    }
