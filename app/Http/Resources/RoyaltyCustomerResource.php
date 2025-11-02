<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Services\CountryCodeService;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class RoyaltyCustomerResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'               => $this->id ,
                'points_formatted' => number_format($this->points) ,
                'points'           => $this->points ,
                'visits'           => $this->visits ,
                'qr_code'          => asset($this->qr_code) ,
                'customer_id'      => $this->customer_id ,
                'royaltyPackage'   => new RoyaltyPackageResource($this->royaltyPackage) ,
                'dob'              => AppLibrary::datetime($this->dob) ,
                'city'             => $this->city ,
                'name'             => $this->name ,
                'referal'          => $this->referal ,
                'country'          => $this->country ,
                'email'            => $this->email ,
                'country_code'     => $this->code() ,
                'phone'            => $this->phone ,
                'status'           => $this->status ,
                'reward_location'  => $this->reward_location ,
                'contact_method'   => $this->contact_method ,
                'info_source'      => $this->info_source ,
                'created_at'       => $this->created_at ,
                'image'            => $this->image ,
            ];
        }

        public function code() : ?string
        {
            $country_code_service = new CountryCodeService();
            foreach ( $country_code_service->getCountriesList() as $country ) {
                if ( $country['country_name'] == $this->country ) {
                    return $country['calling_code'];
                }
            }
            return null;
        }
    }
