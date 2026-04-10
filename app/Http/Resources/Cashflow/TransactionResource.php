<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Cashflow\MotherAccount;
    use App\Models\Transaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Transaction */
    class TransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $balance = $this->entity->balance;
            return [
                'id'            => $this->id ,
                'date'          => datetime( $this->date ) ,
                'cash_type'     => $this->cash_type ,
                'amount'        => $this->amount ,
                'fee'           => $this->fee ,
                'fee_currency'  => currency( $this->fee ) ,
                'description'   => $this->description ,
                'status'        => $this->status ,
                'image'         => $this->image ,
                'exchange_rate' => $this->exchange_rate ,

                'entity_id'               => $this->entity_id ,
                'currency_id'             => $this->currency_id ,
                'transaction_category_id' => $this->transaction_category_id ,
                'cash_in'                 => $this->cash_in > 0 ? currency( $this->cash_in ) : '-' ,
                'cash_out'                => $this->cash_out > 0 ? currency( $this->cash_out ) : '-' ,
                'balance'                 => $balance ,
                'balance_currency'        => currency( $balance ) ,

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
