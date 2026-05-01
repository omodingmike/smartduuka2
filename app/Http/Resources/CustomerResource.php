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
        private Carbon $_now;

        public function __construct($resource)
        {
            parent::__construct( $resource );
            $this->_now = now();
        }

        public function toArray($request) : array
        {
            return [
                'id'              => $this->id ,
                'name'            => ucwords( $this->name ) ,
                'username'        => $this->username ,
                'email'           => $this->email ,
                'type'            => $this->type ,

                // FIX: Map wallet metrics to the actual 'balance' attribute on the User model
                'wallet'          => $this->balance ?? 0 ,
                'wallet_currency' => currency( $this->balance ?? 0 ) ,

                'wallet_transactions' => CustomerWalletTransactionResource::collection(
                    $this->whenLoaded( 'walletTransactions' )
                ) ,

                'debtPaid'          => currency(
                    $this->debt_paid ?? $this->debtPayments->sum( 'amount' )
                ) ,

                // FIX: Align total credit orders with the dynamic column generated in the model's scope
                'totalCreditOrders' => currency(
                    $this->total_order_debt ?? $this->totalCreditOrders ?? 0
                ) ,

                'debtPayments' => CustomerPaymentResource::collection(
                    $this->whenLoaded( 'debtPayments' , fn() => $this->debtPayments )
                ) ,

                'phone'  => $this->phone ?? '' ,
                'status' => $this->status ,

                'credits'                 => $this->total_credits ,
                'total_spent'             => $this->total_spent ,
                'wallet_balance'          => $this->wallet_balance ,
                'wallet_balance_currency' => currency( $this->wallet_balance ) ,
                'total_spent_currency'    => currency( $this->total_spent ) ,
                'credits_currency'        => currency( $this->total_credits ) ,

                'ledgers'             => CustomerLedgerResource::collection(
                    $this->whenLoaded( 'ledgers' )
                ) ,
//                'show_pay'            => $creditsValue > 0 ,
                'show_pay_list'       => $this->whenLoaded(
                    'payments' ,
                    fn() => $this->payments->isNotEmpty() ,
                    FALSE
                ) ,
                'image'               => $this->image ,
                'notes'               => $this->notes ,
                'oldest_credit_order' => $this->oldest_credit_order ,
//                'totalBalance'        => currency( $creditsValue ) ,
                'totalSpent'          => currency( $this->total_spent ?? 0 ) ,
                'addresses'           => AddressResource::collection(
                    $this->whenLoaded( 'addresses' )
                ) ,
                'creditOrders'        => OrderResource::collection( $this->unPaidOrders ) ,
                'creditProfile'       => [] ,
                'created_at'          => AppLibrary::date( $this->created_at ) ,
            ];
        }
    }