<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\CleaningService;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CleaningService */
    class CleaningServiceResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                           => $this->id ,
                'name'                         => $this->name ,
                'price'                        => $this->price ,
                'price_text'                   => AppLibrary::currencyAmountFormat( $this->price ) ,
                'currency'                     => config( 'system.currency_symbol' ) ,
                'description'                  => $this->description ,
                'type'                         => $this->type ,
                'image'                        => $this->image ,
                'tax_id'                       => $this->tax_id ,
                'type_text'                    => $this->type == 0 ? 'Fixed Price' : "Per Unit" ,
                'cleaning_service_category_id' => $this->cleaning_service_category_id ,
                'cleaningServiceCategory'      => new CleaningServiceCategoryResource( $this->whenLoaded( 'cleaningServiceCategory' ) ) ,
                'tax'                          => new TaxResource( $this->tax ) ,
            ];
        }
    }
