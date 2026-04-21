<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ServiceCategoryRequest;
    use App\Http\Resources\ServiceCategoryResource;
    use App\Models\ServiceCategory;
    use App\Traits\SaveMedia;
    use Illuminate\Http\Request;

    class ServiceCategoryController extends Controller
    {
        use SaveMedia;

        public function index(Request $request)
        {
            $search   = $request->input( 'search' );
            $paginate = $request->boolean( 'paginate' );
            $q        = ServiceCategory::query();

            $q->when( $search , function ($qr) use ($search) {
                $qr->where( 'name' , 'ilike' , "%{$search}%" );
            } );

            return ServiceCategoryResource::collection( $paginate ? $q->paginate() : $q->get() );
        }

        public function store(ServiceCategoryRequest $request)
        {
            $c = ServiceCategory::create( $request->validated() );
            $this->saveMedia( $request , $c );
            return new ServiceCategoryResource( $c );
        }

        public function update(ServiceCategoryRequest $request , ServiceCategory $serviceCategory)
        {
            $serviceCategory->update( $request->validated() );
            $this->saveMedia( $request , $serviceCategory );
            return new ServiceCategoryResource( $serviceCategory );
        }

        public function destroy(Request $request)
        {
            ServiceCategory::destroy( $request->ids );

            return response()->json();
        }
    }
