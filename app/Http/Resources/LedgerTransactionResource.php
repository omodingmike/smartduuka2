<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class LedgerTransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'created_at' => AppLibrary::date($this->created_at) ,
                'account'    => $this->ledger->name ,
                'currency'   => $this->ledger->currency->symbol ,
                'narration'  => $this->narration ,
                'credit'     => $this->credit ,
                'debit'      => $this->debit ,
                'balance'    => $this->balance ,
            ];
        }
    }
