<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreSpecialDayRequest;
    use App\Http\Requests\UpdateSpecialDayRequest;
    use App\Http\Resources\SpecialDaysResource;
    use App\Models\SpecialDay;

    class SpecialDayController extends Controller
    {
        public function index()
        {
            return SpecialDaysResource::collection(SpecialDay::all());
        }

        public function store(StoreSpecialDayRequest $request)
        {
            $special_day = SpecialDay::create($request->validated());
            activityLog("Created Special Day: $special_day->name");
            return new SpecialDaysResource($special_day);
        }

        public function show(SpecialDay $specialDay)
        {
            return new SpecialDaysResource($specialDay);
        }

        public function update(UpdateSpecialDayRequest $request , SpecialDay $specialDay)
        {
            $specialDay->update($request->validated());
            activityLog("Updated Special Day: $specialDay->name");
            return new SpecialDaysResource($specialDay);
        }

        public function destroy(SpecialDay $specialDay)
        {
            $specialDay->delete();
            return response()->noContent();
        }
    }
