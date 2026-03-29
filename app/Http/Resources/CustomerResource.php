<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin User
     */
    class CustomerResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         * @mixin User
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            $totalPaid = $this->orders->flatMap( function ($order) {
                return $order->posPayments;
            } )->sum( 'amount' );

            return [
                "id"                  => $this->id ,
                "name"                => ucwords( $this->name ) ,
                "username"            => $this->username ,
                "email"               => $this->email ,
                "type"                => $this->type ,
                "wallet"              => $this->wallet ,
                "wallet_currency"     => currency( $this->wallet ) ,
                "wallet_transactions" => CustomerWalletTransactionResource::collection( $this->whenLoaded( 'walletTransactions' ) ) ,
                "debtPaid"            => currency( $this->debtPayments()->sum( 'amount' ) ) ,
                "totalCreditOrders"   => currency( $this->totalCreditOrders ) ,
                "debtPayments"        => CustomerPaymentResource::collection( $this->debtPayments ) ,
                "phone"               => $this->phone === NULL ? '' : $this->phone ,
                "status"              => $this->status ,
                "credits"             => AppLibrary::currencyAmountFormat( $this->credits ) ,
                "credits_currency"    => currency( $this->credits ) ,
                'ledgers'             => CustomerLedgerResource::collection( $this->ledgers ) ,
                "show_pay"            => $this->credits > 0 ,
                "show_pay_list"       => count( $this->payments ) > 0 ,
                "image"               => $this->image ,
                "notes"               => $this->notes ,
                "oldest_credit_order" => $this->oldest_credit_order ,
                "totalBalance"        => currency( $this->credits ) ,
                "totalSpent"          => AppLibrary::currencyAmountFormat( $totalPaid ) ,
                "addresses"           => AddressResource::collection( $this->addresses ) ,
                'creditOrders'        => $this->credit_orders->sortByDesc( 'id' )->map( function ($order) {
                    $paid = $order->posPayments()->sum( 'amount' );
                    return [
                        'id'               => $order->id ,
                        'order_serial_no'  => $order->order_serial_no ,
                        'order_datetime'   => AppLibrary::datetime2( $order->order_datetime ) ,
                        'age'              => round( Carbon::parse( $order->order_datetime )->diffInHours( now() ) / 24 ) ,
                        'total_amount'     => $order->total ,
                        'paid_currency'    => AppLibrary::currencyAmountFormat( $paid ) ,
                        'total_currency'   => AppLibrary::currencyAmountFormat( $order->total ) ,
                        'balance'          => $order->balance ,
                        'balance_currency' => AppLibrary::currencyAmountFormat( $order->balance ) ,
                        'status'           => [
                            'label' => $order->status->label() ,
                            'value' => $order->status->value
                        ] ,
                        'payment_status'   => [
                            'label' => $order->payment_status->label() ,
                            'value' => $order->payment_status->value
                        ] ,
                        'payment_type'     => [
                            'label' => $order->payment_type->label() ,
                            'value' => $order->payment_type->value
                        ] ,
                        // Summarizing items for the table row
                        'items_count'      => $order->orderProducts->sum( 'quantity' ) ,
                        'items_summary'    => $order->orderProducts->map( function ($op) {
                            return $op?->item?->name . ' (x' . $op?->quantity . ')';
                        } )->implode( ', ' ) ,
                    ];
                } )->values() ,
                "creditProfile"       => [] ,
                "created_at"          => AppLibrary::date( $this->created_at ) ,
            ];
        }
    }
