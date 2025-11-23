<?php

    namespace App\Http\Controllers;

    use App\Enums\MediaEnum;
    use App\Http\Requests\CleaningServiceRequest;
    use App\Http\Resources\CleaningServiceResource;
    use App\Models\CleaningService;
    use App\Models\CleaningServiceCategory;
    use Illuminate\Http\Request;
    use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
    use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

    class CleaningServiceController extends Controller
    {
        public function index()
        {
            $data = CleaningService::with( ['cleaningServiceCategory','tax'] )->get();
            info($data);
            return CleaningServiceResource::collection( CleaningService::with( ['cleaningServiceCategory','tax'] )->get() );
        }

        /**
         * @throws FileDoesNotExist
         * @throws FileIsTooBig
         */
        public function store(CleaningServiceRequest $request)
        {
            $service = CleaningService::create( $request->validated() );
            if ( $request->hasFile( 'image' ) ) {
                $service->addMedia( $request->image )->toMediaCollection( MediaEnum::SERVICES_MEDIA_COLLECTION );
            }
            return new CleaningServiceResource( $service );
        }

        public function show(CleaningService $cleaningService)
        {
            return new CleaningServiceResource( $cleaningService );
        }

        /**
         * @throws FileDoesNotExist
         * @throws FileIsTooBig
         */
        public function update(CleaningServiceRequest $request , CleaningService $cleaningService)
        {
            $cleaningService->update( $request->validated() );

            if ( $request->hasFile( 'image' ) ) {
                $cleaningService->addMedia( $request->image )->toMediaCollection( MediaEnum::SERVICES_MEDIA_COLLECTION );
            }

            return new CleaningServiceResource( $cleaningService );
        }

        public function destroy(Request $request)
        {
            CleaningService::destroy( $request->ids );
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
