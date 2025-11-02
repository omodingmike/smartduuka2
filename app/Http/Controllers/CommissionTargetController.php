<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CommissionTargetRequest;
    use App\Http\Resources\CommissionTargetResource;
    use App\Models\CommissionTarget;

    class CommissionTargetController extends Controller
    {
        public function index()
        {
            return CommissionTargetResource::collection( CommissionTarget::all() );
        }

        public function store(CommissionTargetRequest $request)
        {
            return new CommissionTargetResource( CommissionTarget::create( $request->validated() ) );
        }

        public function show(CommissionTarget $commissionTarget)
        {
            return new CommissionTargetResource( $commissionTarget );
        }

        public function update(CommissionTargetRequest $request , CommissionTarget $commissionTarget)
        {
            $commissionTarget->update( $request->validated() );

            return new CommissionTargetResource( $commissionTarget );
        }

        public function destroy(CommissionTarget $commissionTarget)
        {
            $commissionTarget->delete();

            return response()->json();
        }
    }
