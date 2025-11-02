<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\UpdateRoyaltyPointsExchangeRateRequest;
    use App\Http\Resources\RoyaltyPointsExchangeRateResource;
    use App\Models\RoyaltyPointsExchageRate;

    class RoyaltyPointsExchageRateController extends Controller
    {
        public function index() : RoyaltyPointsExchangeRateResource
        {
            return new RoyaltyPointsExchangeRateResource(RoyaltyPointsExchageRate::first());
        }

        public function update(UpdateRoyaltyPointsExchangeRateRequest $request) : void
        {
            $royaltyPointsExchageRate = RoyaltyPointsExchageRate::find(1);
            if ( $royaltyPointsExchageRate ) {
                $royaltyPointsExchageRate->update($request->validated());
            } else {
                RoyaltyPointsExchageRate::create($request->validated());
            }
        }
    }
