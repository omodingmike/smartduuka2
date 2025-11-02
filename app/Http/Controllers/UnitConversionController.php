<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreUnitConversionRequest;
    use App\Http\Requests\UpdateUnitConversionRequest;
    use App\Http\Resources\UnitConversionResource;
    use App\Models\UnitConversion;

    class UnitConversionController extends Controller
    {
        public function index()
        {
            return UnitConversionResource::collection(UnitConversion::with(['baseUnit','otherUnit'])->get());
//            return response()->json(UnitConversion::with([ 'baseUnit' , 'otherUnit' ])->get());
        }

        public function store(StoreUnitConversionRequest $request)
        {
            $unitConversion = UnitConversion::where('base_unit_id' , $request->base_unit_id)
                                            ->where('other_unit_id' , $request->other_unit_id)
                                            ->first();
            if ( ! $unitConversion ) {
                $unitConversion = UnitConversion::create($request->validated());
            }
            return response()->json($unitConversion , 201);
        }

        public function show(UnitConversion $unitConversion)
        {
            return response()->json($unitConversion);
        }

        public function update(UpdateUnitConversionRequest $request , UnitConversion $unitConversion)
        {
            $unitConversion->update($request->validated());
            return response()->json($unitConversion);
        }

        public function destroy(UnitConversion $unitConversion)
        {
            $unitConversion->delete();
            return response()->json(null , 204);
        }
    }
