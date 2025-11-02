<?php

    namespace App\Http\Controllers\API;

    use App\Enums\DistributionStockStatus;
    use App\Enums\Role as EnumRole;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\DistributionRoute\UpdateDistributionRouteRequest;
    use App\Http\Resources\DistributionRoute\DistributionRouteResource;
    use App\Models\DistributionRoute;
    use App\Models\Stock;
    use App\Models\User;
    use Essa\APIToolKit\Api\ApiResponse;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Support\Collection;

    class DistributionRouteController extends Controller
    {
        use ApiResponse;

        public function __construct() {}

        public function index() : AnonymousResourceCollection
        {
            $distributionRoutes = DistributionRoute::with( [ 'stocks.product.unit' , 'stockSold.product.unit' , 'stockReturned.product.unit' ] )->latest()->useFilters()
                                                   ->dynamicPaginate();
            return DistributionRouteResource::collection( $distributionRoutes );
        }


        public function update(UpdateDistributionRouteRequest $request , DistributionRoute $distributionRoute) : JsonResponse
        {
            $data  = $request->validated();
            $sales = 0;
            foreach ( $data[ 'sold' ] as $stock_id => $sold ) {
                $stock           = Stock::find( $stock_id );
                $remaining_stock = $stock->quantity - $sold;
                $stock->decrement( 'quantity' , $sold );
                $sales += $stock->product->selling_price * $sold;
                if ( $data[ 'destination' ] == 'store' ) {
                    $stock_batch = Stock::where( 'batch' , $data[ 'batch' ] )
                                        ->where( 'id' , '!=' , $stock->id )
                                        ->first();
                    $stock_batch->increment( 'quantity' , $remaining_stock );
                    $stock->decrement( 'quantity' , $remaining_stock );
                }
                $stock->update( [ 'sold' => $sold , 'returned' => $remaining_stock ] );
            }

            $distributionRoute->update( [ 'actual_sales' => $sales , 'status' => DistributionStockStatus::COMPLETED ] );

            return $this->responseSuccess( 'DistributionRoute updated Successfully' , new DistributionRouteResource( $distributionRoute ) );
        }

        public function destroy(DistributionRoute $distributionRoute) : JsonResponse
        {
            $distributionRoute->delete();

            return $this->responseDeleted();
        }

        public function truckStock()
        {
            $distributorUsers = User::with( [ 'roles' , 'stocks.product.unit' ] )
                                    ->whereHas( 'roles' , fn($query) => $query->where( 'id' , EnumRole::DISTRIBUTOR ) )
                                    ->get();

            $truckStockData = $distributorUsers->map( fn($user) => [
                'user_id'   => $user->id ,
                'user_name' => $user->name ,
                'stocks'    => $user->stocks()->where( 'quantity' , '>' , 0 )->get()
                                    ->groupBy( function ($stock) {
                                        $displayName = $stock->product->name ?? 'Unknown Product';
                                        if ( ! empty( $stock->variation_names ) ) {
                                            $displayName .= ' - (' . $stock->variation_names . ')';
                                        }
                                        $unitCode = $stock->product->unit->code ?? 'UNIT';

                                        return $displayName . '|' . $unitCode;
                                    } )
                                    ->map( function (Collection $groupedStocks) {

                                        $firstStock = $groupedStocks->first();

                                        $totalQuantity = $groupedStocks->sum( fn($s) => (float) $s->quantity );

                                        $displayName = $firstStock->product->name ?? 'Unknown Product';
                                        if ( ! empty( $firstStock->variation_names ) ) {
                                            $displayName .= ' - (' . $firstStock->variation_names . ')';
                                        }
                                        $unitCode = $firstStock->product->unit->code ?? 'UNIT';

                                        $formattedQuantity = number_format( $totalQuantity , 2 , '.' , '' );

                                        return [
                                            'display_name'       => $displayName ,
                                            'quantity_with_unit' => $formattedQuantity . ' ' . $unitCode ,
                                            'quantity'           => $formattedQuantity ,
                                            'unit_code'          => $unitCode ,
                                        ];
                                    } )
                                    ->values() ,
            ] )->values();
            return $this->responseSuccess( NULL , $truckStockData );
        }
    }
