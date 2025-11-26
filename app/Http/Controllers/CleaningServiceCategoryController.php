<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningServiceCategoryRequest;
    use App\Http\Resources\CleaningServiceCategoryResource;
    use App\Models\CleaningServiceCategory;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class CleaningServiceCategoryController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            return CleaningServiceCategoryResource::collection( $this->filter( CleaningServiceCategory::with( 'services' ) , $request ) );
        }

        public function list()
        {
            return CleaningServiceCategoryResource::collection( CleaningServiceCategory::with( 'services' )->get() );
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

    }
