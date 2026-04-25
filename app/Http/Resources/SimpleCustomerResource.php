<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin User
     */
    class SimpleCustomerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $creditOrders = $this->whenLoaded(
                'creditAndDeposit' ,
                function () {
                    return $this->creditAndDeposit
                        ->sortByDesc( 'id' )
                        ->map( function ($order) {
                            $paid       = $order->total_paid ?? 0;
                            $itemsCount = $order->items_count ?? 0;

                            return [
                                'id'               => $order->id ,
                                'order_serial_no'  => $order->order_serial_no ,
                                'order_datetime'   => datetime( $order->order_datetime ) ,
                                'age'              => round( Carbon::parse( $order->order_datetime )->diffInHours( $this->_now ) / 24 ) ,
                                'total_amount'     => $order->total ,
                                'paid_currency'    => AppLibrary::currencyAmountFormat( $paid ) ,
                                'total_currency'   => AppLibrary::currencyAmountFormat( $order->total ) ,
                                'balance'          => $order->balance ,
                                'balance_currency' => AppLibrary::currencyAmountFormat( $order->balance ) ,
                                'status'           => [
                                    'label' => $order->status->label() ,
                                    'value' => $order->status->value ,
                                ] ,
                                'payment_status'   => [
                                    'label' => $order->payment_status->label() ,
                                    'value' => $order->payment_status->value ,
                                ] ,
                                'payment_type'     => [
                                    'label' => $order->payment_type->label() ,
                                    'value' => $order->payment_type->value ,
                                ] ,

                                // Use the DB-computed item count
                                'items_count'      => $itemsCount ,

                                // String formatting stays in PHP, but runs faster because
                                // we only loaded the 'id', 'name', and 'quantity' columns
                                'items_summary'    => $order->orderProducts
                                    ->map( fn($op) => ( $op->item?->name ?? '?' ) . ' (x' . $op->quantity . ')' )
                                    ->implode( ', ' ) ,
                            ];
                        } )
                        ->values();
                } ,
                []
            );
            return [
                'id'                      => $this->id ,
                'name'                    => $this->name ,
                'email'                   => $this->email ?? '' ,
                'phone'                   => $this->phone ?? '' ,
                'type'                    => $this->type ,
                'phone2'                  => $this->phone2 ,
                'debtPayments'            => CustomerPaymentResource::collection(
                    $this->whenLoaded( 'debtPayments' , fn() => $this->debtPayments )
                ) ,
                'ledgers'                 => CustomerLedgerResource::collection(
                    $this->whenLoaded( 'ledgers' )
                ) ,
                'credits_currency'        => currency( $this->credits ) ,
                'notes'                   => $this->notes ,
                'credits'                 => $this->credits ,
                'order_count'             => $this->order_count ,
                'total_spent'             => $this->total_spent ?? 0 ,
                'total_spent_currency'    => currency( $this->total_spent ?? 0 ) ,
                'wallet_balance'          => $this->wallet ?? 0 ,
                'wallet_balance_currency' => currency( $this->wallet ?? 0 ) ,
                'status'                  => $this->status ,
                'wallet'                  => $this->wallet ?? 0 ,
                'creditOrders'            => $creditOrders ,
                'wallet_currency'         => currency( $this->wallet ?? 0 ) ,
            ];
        }
    }
