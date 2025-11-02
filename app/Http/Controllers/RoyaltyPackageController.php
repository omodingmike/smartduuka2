<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreRoyaltyPackageRequest;
    use App\Http\Requests\UpdateRoyaltyPackageBenefitsRequest;
    use App\Http\Requests\UpdateRoyaltyPackageRequest;
    use App\Http\Resources\RoyaltyPackageResource;
    use App\Models\RoyaltyPackage;

    class RoyaltyPackageController extends Controller
    {

        public function index()
        {
            return RoyaltyPackageResource::collection(RoyaltyPackage::with('benefits')->get());
        }


        public function store(StoreRoyaltyPackageRequest $request)
        {
            $package = RoyaltyPackage::create($request->validated());
            activityLog("Created Royalty Package: $package->name");
            return new RoyaltyPackageResource($package);
        }

        public function show(RoyaltyPackage $royaltyPackage)
        {
            return new RoyaltyPackageResource($royaltyPackage);
        }

        public function update(UpdateRoyaltyPackageRequest $request , RoyaltyPackage $royaltyPackage)
        {
            $royaltyPackage->update($request->validated());
            activityLog("Updated Royalty Package: $royaltyPackage->name");
            return new RoyaltyPackageResource($royaltyPackage);
        }

        public function updateBenefits(UpdateRoyaltyPackageBenefitsRequest $request , RoyaltyPackage $royaltyPackage)
        {
            $royaltyPackage->benefits()->sync($request->benefits);
            activityLog("Updated Royalty Package Benefits for : $royaltyPackage->name");
            return new RoyaltyPackageResource($royaltyPackage);
        }

        public function destroy(RoyaltyPackage $royaltyPackage)
        {
            $royaltyPackage->delete();
            activityLog("Deleted Royalty Package: $royaltyPackage->name");
            return response()->noContent();
        }
    }
