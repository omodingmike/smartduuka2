<?php

    namespace App\Http\Controllers;

    use App\Enums\Role;
    use App\Enums\Role as EnumRole;
    use App\Http\Requests\CommissionRequest;
    use App\Http\Resources\CommissionResource;
    use App\Http\Resources\CommissionSummaryResource;
    use App\Libraries\AppLibrary;
    use App\Models\Commission;
    use App\Models\CommissionPayout;
    use App\Models\CommissionTarget;
    use App\Models\Product;
    use App\Models\User;
    use Essa\APIToolKit\Api\ApiResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;

    class CommissionController extends Controller
    {
        use ApiResponse;

        public function index(Request $request)
        {
            $commissions = Commission::with( [
                'targets.user' ,
                'targets.role' ,
                'targets.product' ,
                'targets.productVariation'
            ] )->orderBy( 'id' , 'desc' )->get();

            return CommissionResource::collection( $commissions );
        }


        public function store(CommissionRequest $request)
        {
            return DB::transaction( function () use ($request) {
                $data = $request->validated();

                // Determine product scope
                $productScope = $data[ 'applies_to_products' ] === 'all_products'
                    ? 'all_products'
                    : 'specific_products';

                // Create commission
                $commission = Commission::create( [
                    'name'             => $data[ 'name' ] ,
                    'commission_type'  => $data[ 'commission_type' ] ,
                    'commission_value' => $data[ 'commission_value' ] ,
                    'applies_to'       => $data[ 'applies_to' ] ,
                    'product_scope'    => $productScope ,
                ] );

                $targets = [];

                // Decode products if provided
                $products = [];
                if ( $request->filled( 'products' ) ) {
                    $products = json_decode( $request->products , TRUE ) ?? [];
                }

                // Helper for adding target
                $addTarget = function (
                    $userId ,
                    $roleId ,
                    $productId ,
                    $variationId = NULL ,
                    $variationLabel = NULL
                ) use (&$targets , $commission) {
                    // Prevent duplicates
                    $exists = collect( $targets )->first( fn($t) => $t[ 'user_id' ] === $userId &&
                        $t[ 'role_id' ] === $roleId &&
                        $t[ 'product_id' ] === $productId &&
                        $t[ 'product_variation_id' ] === $variationId
                    );
                    if ( ! $exists ) {
                        $targets[] = [
                            'commission_id'        => $commission->id ,
                            'user_id'              => $userId ,
                            'role_id'              => $roleId ,
                            'product_id'           => $productId ,
                            'product_variation_id' => $variationId ,
                            'variation_label'      => $variationLabel ,
                            'created_at'           => now() ,
                            'updated_at'           => now() ,
                        ];
                    }
                };

                /**
                 * === LOAD ALL PRODUCTS (when all products selected) ===
                 * Products with variations → variations take precedence
                 */
                $allProducts = $productScope === 'all_products'
                    ? Product::with( 'variations' )->get()
                    : collect();

                // Determine user/role context
                $userId = $data[ 'applies_to' ] === 'user' ? ( $data[ 'user_id' ] ?? NULL ) : NULL;
                $roleId = $data[ 'applies_to' ] === 'role' ? ( $data[ 'role_id' ] ?? NULL ) : NULL;

                /**
                 * === Handle Specific Products Case ===
                 */
                if ( $productScope === 'specific_products' ) {
                    foreach ( $products as $item ) {
                        $productId      = $item[ 'product_id' ];
                        $isVariation    = $item[ 'is_variation' ];
                        $variationId    = $isVariation ? $item[ 'variation_id' ] : NULL;
                        $variationLabel = $item[ 'variation_names' ] ?? NULL;

                        // Check if product has variations
                        $product = Product::with( 'variations' )->find( $productId );

                        if ( $isVariation ) {
                            // Use provided variation
                            $addTarget( $userId , $roleId , $productId , $variationId , $variationLabel );
                        }
                        elseif ( $product && $product->variations->count() > 0 ) {
                            // If product has variations → assign all variations instead
                            foreach ( $product->variations as $variation ) {
                                $addTarget( $userId , $roleId , $product->id , $variation->id , $variation->name );
                            }
                        }
                        else {
                            // No variations → assign product itself
                            $addTarget( $userId , $roleId , $productId );
                        }
                    }
                }

                /**
                 * === Handle All Products Case ===
                 */
                if ( $productScope === 'all_products' ) {
                    foreach ( $allProducts as $product ) {
                        if ( $product->variations->count() > 0 ) {
                            foreach ( $product->variations as $variation ) {
                                $addTarget( $userId , $roleId , $product->id , $variation->id , $variation->name );
                            }
                        }
                        else {
                            $addTarget( $userId , $roleId , $product->id );
                        }
                    }
                }

                /**
                 * === Handle "All Users" Case ===
                 * (applies_to = users)
                 */
                if ( $data[ 'applies_to' ] === 'users' ) {
                    if ( $productScope === 'specific_products' ) {
                        foreach ( $products as $item ) {
                            $productId      = $item[ 'product_id' ];
                            $isVariation    = $item[ 'is_variation' ];
                            $variationId    = $isVariation ? $item[ 'variation_id' ] : NULL;
                            $variationLabel = $item[ 'variation_names' ] ?? NULL;

                            $product = Product::with( 'variations' )->find( $productId );

                            if ( $isVariation ) {
                                $addTarget( NULL , NULL , $productId , $variationId , $variationLabel );
                            }
                            elseif ( $product && $product->variations->count() > 0 ) {
                                foreach ( $product->variations as $variation ) {
                                    $addTarget( NULL , NULL , $product->id , $variation->id , $variation->name );
                                }
                            }
                            else {
                                $addTarget( NULL , NULL , $productId );
                            }
                        }
                    }
                    else {
                        foreach ( $allProducts as $product ) {
                            if ( $product->variations->count() > 0 ) {
                                foreach ( $product->variations as $variation ) {
                                    $addTarget( NULL , NULL , $product->id , $variation->id , $variation->name );
                                }
                            }
                            else {
                                $addTarget( NULL , NULL , $product->id );
                            }
                        }
                    }
                }

                // === Apply Precedence (Optional Conflict Cleanup) ===
                // Delete broader commissions if this one is more specific
                // Example: user-specific commission overrides global
                CommissionTarget::where( function ($q) use ($userId , $roleId) {
                    if ( $userId ) {
                        $q->whereNull( 'user_id' )
                          ->orWhere( 'role_id' , '!=' , $roleId );
                    }
                    elseif ( $roleId ) {
                        $q->whereNull( 'role_id' );
                    }
                } )
                                ->whereIn( 'product_id' , collect( $targets )->pluck( 'product_id' ) )
                                ->delete();

                // === Save all targets ===
                if ( ! empty( $targets ) ) {
                    CommissionTarget::insert( $targets );
                }

                $commission->load( [ 'targets.user' , 'targets.role' , 'targets.product' , 'targets.productVariation' ] );

                return new CommissionResource( $commission );
            } );
        }

        public function commissionSummary()
        {
            $users = User::with( 'payouts' )->whereHas( 'roles' , function ($query) {
                $query->where( 'id' , '!=' , EnumRole::ADMIN );
                $query->where( 'id' , '!=' , EnumRole::CUSTOMER );
            } )->where( 'commission' , '>' , 0 )->get();
            return CommissionSummaryResource::collection( $users );
        }

        public function show(Commission $commission)
        {
            return new CommissionResource( $commission );
        }

        public function destroy(Commission $commission)
        {
            $commission->delete();
            return response()->json();
        }

        public function commissionSummaryDashboard()
        {
            $total_commission = User::whereHas( 'roles' , function ($query) {
                $query->where( 'id' , Role::DISTRIBUTOR );
            } )->get()->sum( function (User $user) {
                return $user->commission;
            } );
            $total_payouts    = CommissionPayout::sum( 'amount' );
            return $this->responseSuccess(
                data: [
                    'total_commission' => AppLibrary::currencyAmountFormat( $total_commission ) ,
                    'total_payouts'    => AppLibrary::currencyAmountFormat( $total_payouts ) ,
                    'balance'          => AppLibrary::currencyAmountFormat( $total_commission - $total_payouts ) ,
                ]
            );
        }
    }
