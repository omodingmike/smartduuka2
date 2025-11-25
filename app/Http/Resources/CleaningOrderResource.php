<?php

    namespace App\Http\Resources;

    use App\Enums\CleaningOrderStatus;
    use App\Libraries\AppLibrary;
    use App\Models\CleaningOrder;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CleaningOrder */
    class CleaningOrderResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'             => $this->id ,
                'order_id'       => $this->order_id ,
                'total'          => $this->total ,
                'address'        => $this->address ?? '' ,
                'total_text'     => AppLibrary::currencyAmountFormat( $this->total ) ,
                'subtotal'       => AppLibrary::currencyAmountFormat( $this->subtotal ) ,
                'tax'            => AppLibrary::currencyAmountFormat( $this->tax ) ,
                'discount'       => AppLibrary::currencyAmountFormat( $this->discount ) ,
                'paid'           => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'balance'        => AppLibrary::currencyAmountFormat( $this->balance ) ,
                'date'           => AppLibrary::datetime2( $this->date ) ,
                'status'         => [
                    'value'   => $this->status->value ,
                    'label'   => $this->status->label() ,
                    'color'   => $this->status->color() ,
                    'options' => CleaningOrderStatus::options()
                ] ,
                'service_method' => [
                    'value' => $this->service_method->value ,
                    'label' => $this->service_method->label() ,
                    'steps' => $this->service_method->steps() ,
                ] ,
                'items' => CleaningOrderItemResource::collection($this->whenLoaded('items')),
                'cleaning_service_customer_id' => $this->cleaning_service_customer_id ,
                'cleaning_service_id'          => $this->cleaning_service_id ,
                'payment_method_id'            => $this->payment_method_id ,
                'payment_method'               => new PaymentMethodResource( $this->paymentMethod ) ,

                'cleaningServiceCustomer' => new CleaningServiceCustomerResource( $this->whenLoaded( 'cleaningServiceCustomer' ) ) ,
            ];
        }
    }
