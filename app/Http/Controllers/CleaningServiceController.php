<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningServiceRequest;
    use App\Http\Resources\CleaningServiceResource;
    use App\Models\CleaningService;
    use App\Models\CleaningServiceCategory;
    use Illuminate\Http\Request;

    class CleaningServiceController extends Controller
    {
        public function index()
        {
            return CleaningServiceResource::collection( CleaningService::with( 'cleaningServiceCategory' )->get() );
        }

        public function store(CleaningServiceRequest $request)
        {
            $service = CleaningService::create( $request->validated() );
//            if ( $request->hasFile( 'image' ) ) {
//                $service->addMedia( $request->image )->toMediaCollection( 'service' );
//            }
            return new CleaningServiceResource( $service );
        }

        public function show(CleaningService $cleaningService)
        {
            return new CleaningServiceResource( $cleaningService );
        }

        public function update(CleaningServiceRequest $request , CleaningService $cleaningService)
        {
            $cleaningService->update( $request->validated() );

//            if ( $request->hasFile( 'image' ) ) {
//                $cleaningService->addMedia( $request->image )->toMediaCollection( 'service' );
//            }

            return new CleaningServiceResource( $cleaningService );
        }

        public function destroy(CleaningService $cleaningService)
        {
            $cleaningService->delete();

            return response()->json();
        }

        public function cleaningServicesByCategory(string $category)
        {
            $category = CleaningServiceCategory::find( $category );

            if ( $category ) {
                return CleaningServiceResource::collection( $category->services );
            }
            return CleaningServiceResource::collection( collect() );
        }

    }
