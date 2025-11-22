<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningServiceCategoryRequest;
    use App\Http\Resources\CleaningServiceCategoryResource;
    use App\Models\CleaningServiceCategory;
    use Illuminate\Http\Request;

    class CleaningServiceCategoryController extends Controller
    {
        public function index()
        {
            return CleaningServiceCategoryResource::collection( CleaningServiceCategory::all() );
        }

        public function store(CleaningServiceCategoryRequest $request)
        {
            return new CleaningServiceCategoryResource( CleaningServiceCategory::create( $request->validated() ) );
        }

        public function show(CleaningServiceCategory $cleaningServiceCategory)
        {
            return new CleaningServiceCategoryResource( $cleaningServiceCategory );
        }

        public function update(CleaningServiceCategoryRequest $request , CleaningServiceCategory $cleaningServiceCategory)
        {
            $cleaningServiceCategory->update( $request->validated() );

            return new CleaningServiceCategoryResource( $cleaningServiceCategory );
        }

        public function destroy(Request $request)
        {
            CleaningServiceCategory::destroy( $request->ids );

            return response()->json();
        }

        public function list() {}
    }
