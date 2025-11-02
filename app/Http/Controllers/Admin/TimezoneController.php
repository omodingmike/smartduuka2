<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Resources\TimezoneResource;
    use App\Services\TimezoneService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;

    class TimezoneController extends AdminController
    {
        private TimezoneService $timezoneService;

        public function __construct(TimezoneService $timezoneService)
        {
            parent::__construct();
            $this->timezoneService = $timezoneService;
        }

        public function index() : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return TimezoneResource::collection( $this->timezoneService->list() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
