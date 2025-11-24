<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\CleaningSettingRequest;
    use App\Http\Requests\SiteRequest;
    use App\Http\Resources\CleaningSettingResource;
    use App\Http\Resources\SiteResource;
    use App\Services\SiteService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Response;

    class SiteController extends AdminController
    {
        public SiteService $siteService;

        public function __construct(SiteService $siteService)
        {
            parent::__construct();
            $this->siteService = $siteService;
//        $this->middleware(['permission:settings'])->only('update');
        }

        public function index() : SiteResource | Response | Application | ResponseFactory
        {
            try {
                return new SiteResource( $this->siteService->list() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function cleaningIndex()
        {
            try {
                return new CleaningSettingResource( $this->siteService->CleaningSettingList() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(SiteRequest $request) : SiteResource | Response | Application | ResponseFactory
        {
            try {
                return new SiteResource( $this->siteService->update( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function updateCleaning(CleaningSettingRequest $request)
        {
            try {
                return new CleaningSettingResource( $this->siteService->updateCleaning( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
