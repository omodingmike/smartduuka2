<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\MotherAccount;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin \App\Models\Cashflow\Transaction */
    class TransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $runningBalance = $this->running_balance_2;
            return [
                'id'                       => $this->id ,
                'date'                     => datetime( $this->date ) ,
                'cash_type'                => $this->cash_type ,
                'amount'                   => $this->amount ,
                'amount_currency'          => currency( $this->amount ) ,
                'fee'                      => $this->fee ,
                'fee_currency'             => currency( $this->fee ) ,
                'description'              => $this->description ,
                'status'                   => $this->status ,
                'image'                    => $this->image ,
                'reference'                => $this->reference ,
                'exchange_rate'            => $this->exchange_rate ,
                'running_balance'          => $runningBalance ,
                'running_balance_currency' => currency( $runningBalance ) ,

                'entity_id'               => $this->entity_id ,
                'currency_id'             => $this->currency_id ,
                'transaction_category_id' => $this->transaction_category_id ,
                'cash_in'                 => $this->cash_in > 0 ? currency( $this->cash_in ) : '-' ,
                'cash_out'                => $this->cash_out > 0 ? currency( $this->cash_out ) : '-' ,
                'balance'                 => $runningBalance ,
                'balance_currency'        => currency( $runningBalance ) ,

                'entity'              => new EntityResource( $this->whenLoaded( 'entity' ) ) ,
                'transactionCategory' => new TransactionCategoryResource( $this->whenLoaded( 'transactionCategory' ) ) ,
                'currency'            => new CurrencyResource( $this->whenLoaded( 'currency' ) ) ,
                'account'             => $this->whenLoaded( 'accountable' , function () {
                    $prefix      = $this->accountable_type === MotherAccount::class ? 'mother' : 'sub';
                    $accountType = class_basename( $this->accountable_type );
                    $resource    = 'App\\Http\\Resources\\Cashflow\\' . $accountType . 'Resource';
                    return [
                        'id'      => $prefix . '-' . $this->accountable->id ,
                        'name'    => $this->accountable->name ,
                        'details' => new $resource( $this->accountable ) ,
                    ];
                } ) ,
            ];
        }
    }
