<?php

    namespace App\Http\Resources;

    use App\Models\Commission;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Commission */
    class CommissionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => $this->id ,
                'name'                => $this->name ,
                'commission_type'     => $this->commission_type ,
                'commission_value'    => $this->commission_value ,
                'applies_to'          => $this->applies_to ,
                'product_scope'       => $this->product_scope ,

                // === Assigned To Label ===
                'assigned_to'         => $this->whenLoaded( 'targets' , function () {
                    // We take first target as representative (since all targets share same applies_to)
                    $target = $this->targets->first();

                    if ( ! $target ) {
                        return NULL;
                    }

                    if ( $target->user ) {
                        return $target->user->name;
                    }

                    if ( $target->role ) {
                        return $target->role->name;
                    }

                    if ( is_null( $target->user_id ) && is_null( $target->role_id ) ) {
                        return 'All Users';
                    }

                    return NULL;
                } ) ,

                // === Product Scope Label ===
                'product_scope_label' => $this->whenLoaded( 'targets' , function () {
                    if ( $this->product_scope === 'all_products' ) {
                        return 'All Products';
                    }

                    return $this->targets
                        ->map( function ($target) {
                            if ( $target->variation_label ) {
                                return "{$target->product->name} - {$target->variation_label}";
                            }

                            if ( $target->product ) {
                                return $target->product->name;
                            }

                            return NULL;
                        } )
                        ->filter()
                        ->unique()
                        ->implode( ', ' );
                } ) ,

                'is_active' =>  $this->is_active ,

                'targets' => $this->whenLoaded( 'targets' , fn() => $this->targets->map( fn($t) => [
                    'id'   => $t->id ,
                    'user' => $t->user ? [
                        'id'   => $t->user->id ,
                        'name' => $t->user->name ,
                    ] : ( $t->role ? [
                        'id'   => $t->role->id ,
                        'name' => $t->role->name ,
                    ] : ( $t->user_id === NULL && $t->role_id === NULL ? 'All Users' : NULL ) ) ,

                    'product' => $t->product ? [
                        'id'   => $t->product->id ,
                        'name' => $t->product->name ,
                    ] : ( $t->product_id === NULL ? [ 'name' => 'All Products' ] : NULL ) ,

                    'product_variation' => $t->productVariation ? [
                        'id'   => $t->productVariation->id ,
                        'name' => $t->productVariation->name ,
                    ] : NULL ,

                    'variation_label' => $t->variation_label ,
                ] ) ) ,

                'created_at' => $this->created_at?->toDateTimeString() ,
                'updated_at' => $this->updated_at?->toDateTimeString() ,
            ];
        }
    }
