<?php

    namespace App\Http\Controllers\Admin;

    use App\Helpers\SMS;
    use App\Http\Requests\SmsGatewayRequest;
    use App\Http\Resources\SmsGatewayResource;
    use App\Jobs\SendTestSmsJob;
    use App\Services\SmsGatewayService;
    use Exception;
    use Illuminate\Http\Request;

    class SmsGatewayController extends AdminController
    {
        use SMS;

        public SmsGatewayService $smsGatewayService;

        public function __construct(SmsGatewayService $smsGatewayService)
        {
            parent::__construct();
            $this->smsGatewayService = $smsGatewayService;
//        $this->middleware(['permission:settings'])->only('update');
        }

        public function index()
        {
            try {
                return new SmsGatewayResource( $this->smsGatewayService->list() );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(SmsGatewayRequest $request)
        {
            try {
                return new SmsGatewayResource( $this->smsGatewayService->update( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function test(Request $request)
        {
            SendTestSmsJob::dispatch( $request->phone , $request->message );
            return response()->json( [ 'message' => 'sms sent' ] );
        }
    }
