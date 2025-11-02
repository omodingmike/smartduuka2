<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreRoyaltyPackageBenefitRequest;
    use App\Http\Requests\UpdateRoyaltyPackageBenefitRequest;
    use App\Http\Resources\RoyaltyBenefitResouce;
    use App\Models\RoyaltyPackageBenefit;

    class RoyaltyPackageBenefitController extends Controller
    {
        public function index()
        {
            return RoyaltyBenefitResouce::collection(RoyaltyPackageBenefit::all());
        }

        public function store(StoreRoyaltyPackageBenefitRequest $request)
        {
            return new RoyaltyBenefitResouce(RoyaltyPackageBenefit::create($request->validated()));
        }

        public function update(UpdateRoyaltyPackageBenefitRequest $request , RoyaltyPackageBenefit $royaltyBenefit)
        {
            $royaltyBenefit->update($request->validated());
            return new RoyaltyBenefitResouce($royaltyBenefit);
        }


        public function destroy(RoyaltyPackageBenefit $royaltyBenefit)
        {
            $royaltyBenefit->delete();
            return response()->noContent();
        }
    }
