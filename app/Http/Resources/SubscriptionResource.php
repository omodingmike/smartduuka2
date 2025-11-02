<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Subscription;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class SubscriptionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $has_active = Subscription::where('expires_at' , '>=' , now())
                                      ->where('status' , 'active')
                                      ->where('project_id' , config('app.project_id'))
                                      ->exists();
            return [
                'id'                    => $this->id ,
                'plan'                  => $this->plan->name ,
                'invoice_no'            => $this->invoice_no ,
                'phone'                 => $this->phone ,
                'status'                => $this->status ,
                'has_active'            => $has_active ,
                'amount'                => AppLibrary::currencyAmountFormat($this->amount) ,
                'expires_at'            => $this->expires_at ? AppLibrary::datetime2($this->expires_at) : "" ,
                'starts_at'             => $this->starts_at ? AppLibrary::datetime2($this->starts_at) : "" ,
                'external_id'           => $this->external_id ,
                'vendor_transaction_id' => $this->vendor_transaction_id ,
                'payment_status'        => $this->payment_status ,
                'vendor_message'        => $this->vendor_message ,
                'created_at'            => AppLibrary::datetime2($this->created_at) ,
                'updated_at'            => $this->updated_at ,
            ];
        }
    }
