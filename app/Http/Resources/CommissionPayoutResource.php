<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\CommissionPayout;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CommissionPayout */
    class CommissionPayoutResource extends JsonResource
    {
        public function toArray($request)
        {
            // Determine recipient label dynamically
            $recipientLabel = match ( $this->applies_to ) {
                'user'  => $this->user?->name ?? 'Unknown User' ,
                'role'  => $this->role?->name ?? 'Unknown Role' ,
                'users' => 'All Users' ,
                default => 'N/A' ,
            };

            return [
                'id'         => $this->id ,
                'applies_to' => $this->applies_to ,
                'recipient'  => $recipientLabel ,
                'amount'     => AppLibrary::currencyAmountFormat( $this->amount ) ,

                'user' => $this->when( $this->user , fn() => [
                    'id'   => $this->user->id ,
                    'name' => $this->user->name ,
                ] ) ,

                'role' => $this->when( $this->role , fn() => [
                    'id'   => $this->role->id ,
                    'name' => $this->role->name ,
                ] ) ,

                'date'       => AppLibrary::datetime2( $this->date ) ,
                'created_at' => $this->created_at?->toDateTimeString() ,
            ];
        }
    }
