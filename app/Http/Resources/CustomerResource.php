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
        public function toArray($request) : array
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
                'id'                  => $this->id ,
                'name'                => ucwords( $this->name ) ,
                'username'            => $this->username ,
                'email'               => $this->email ,
                'type'                => $this->type ,
                'wallet'              => $this->wallet ,
                'wallet_currency'     => currency( $this->wallet ) ,
                'wallet_transactions' => CustomerWalletTransactionResource::collection(
                    $this->whenLoaded( 'walletTransactions' )
                ) ,

                'debtPaid'            => currency( $this->debtPayments->sum( 'amount' ) ) ,
                'totalCreditOrders'   => currency( $this->totalCreditOrders ) ,
                'debtPayments'        => CustomerPaymentResource::collection(
                    $this->whenLoaded( 'debtPayments' , fn() => $this->debtPayments )
                ) ,
                'phone'               => $this->phone ?? '' ,
                'status'              => $this->status ,
                'credits'             => $this->credits ,
                'credits_currency'    => currency( $this->credits ) ,
                'ledgers'             => CustomerLedgerResource::collection(
                    $this->whenLoaded( 'ledgers' )
                ) ,
                'show_pay'            => $this->credits > 0 ,
                'show_pay_list'       => $this->whenLoaded(
                    'payments' ,
                    fn() => $this->payments->isNotEmpty() ,
                    FALSE
                ) ,
                'image'               => $this->image ,
                'notes'               => $this->notes ,
                'oldest_credit_order' => $this->oldest_credit_order ,
                'totalBalance'        => currency( $this->credits ) ,
                'totalSpent'          => currency( $this->total_spent ?? 0 ) ,
                'addresses'           => AddressResource::collection(
                    $this->whenLoaded( 'addresses' )
                ) ,
                'creditOrders'        => $creditOrders ,
                'creditProfile'       => [] ,
                'created_at'          => AppLibrary::date( $this->created_at ) ,
            ];
        }

        private Carbon $_now;

        public function __construct($resource)
        {
            parent::__construct( $resource );
            $this->_now = now();
        }
    }