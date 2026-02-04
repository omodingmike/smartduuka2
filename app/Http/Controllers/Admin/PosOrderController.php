<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\SettingsEnum;
    use App\Exports\OrderExport;
    use App\Http\Requests\OrderStatusRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PaymentStatusRequest;
    use App\Http\Resources\OrderDetailsResource;
    use App\Http\Resources\OrderResource;
    use App\Jobs\SendInvoiceMailJob;
    use App\Models\Order;
    use App\Models\OrderProduct;
    use App\Services\OrderService;
    use App\Services\PdfExportService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\File;
    use Maatwebsite\Excel\Facades\Excel;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Browsershot\Browsershot;
    use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class PosOrderController extends AdminController
    {
        private OrderService $orderService;

        public function __construct(OrderService $order)
        {
            parent::__construct();
            $this->orderService = $order;
            $this->middleware( [ 'permission:pos-orders' ] )->only( 'index' , 'show' , 'destroy' , 'export' , 'changeStatus' , 'changePaymentStatus' );
        }

        public function index(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function indexCredit(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->listCredits( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function indexQuotations(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->listQuotations( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function indexDeposit(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return OrderResource::collection( $this->orderService->listDeposits( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(Order $order) : Response | OrderDetailsResource | Application | ResponseFactory
        {
            try {
                return new OrderDetailsResource( $this->orderService->show( $order , FALSE ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function pdf(Request $request , Order $order)
        {
            $pdfExportService = new PdfExportService();
            return $pdfExportService->exportOrder( $request->merge( [ 'id' => $order->id ] ) , TRUE );
        }

        public function destroy(Request $request) : Response | Application | ResponseFactory
        {
            try {
                Order::destroy( $request->ids );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function export(PaginateRequest $request) : Response | BinaryFileResponse | Application | ResponseFactory
        {
            try {
                return Excel::download( new OrderExport( $this->orderService , $request ) , 'Pos-Order.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function exportOrder(Request $request)
        {
            $pdfExportService = new PdfExportService();
            return $pdfExportService->exportOrder( $request );
        }

        /**
         * @throws CouldNotTakeBrowsershot
         */
        public function storePDF(string $html , $name)
        {
            $path = storage_path( "/app/reports" );
            if ( ! File::exists( $path ) ) {
                File::makeDirectory( $path , 0777 , TRUE );
            }
            $browserShot = Browsershot::html( $html )
                                      ->showBackground()
                                      ->format( 'A4' );

            if ( ! config( 'app.dev' ) ) {
                $browserShot->setChromePath( config( 'app.chrome_path' ) );
            }

            $browserShot->savePdf( storage_path( "/app/reports/$name.pdf" ) );
        }

        public function mailQuotation(Request $request)
        {
            try {
                $pdfExportService = new PdfExportService();
                $html             = $pdfExportService->renderHtml( $request );
                $order            = Order::find( $request->id );
                $this->storePDF( $html , orderName( $order ) );
                SendInvoiceMailJob::dispatchAfterResponse( $order );
                return response( [ 'data' => [ 'message' => 'Failed to send email' ] ] , 202 );
            } catch ( Exception $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        public function updateCssVariables(Request $request)
        {
            try {
                $this->updateColors( $request );
                return response( '' , 204 );
            } catch ( Exception $e ) {
                return response( [ 'status' => FALSE , 'message' => $e->getMessage() ] , 422 );
            }
        }

        public function updateColors(Request $request)
        {
            $primaryColor   = $request->primaryColor;
            $primaryLight   = $request->primaryLight;
            $secondaryColor = $request->secondaryColor;
            $secondaryLight = $request->secondaryLight;
            Settings::group( SettingsEnum::APP_SETTINGS() )->set(
                [ 'primaryColor'   => $primaryColor ,
                  'primaryLight'   => $primaryLight ,
                  'secondaryColor' => $secondaryColor ,
                  'secondaryLight' => $secondaryLight ,
                ]
            );
        }

        public function changeStatus(Order $order , OrderStatusRequest $request)
        {
            try {
//                return new OrderDetailsResource($this->orderService->changeStatus($order , $request , false));
                return $this->orderService->updateStatus( $order , $request );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function changePaymentStatus(Order $order , PaymentStatusRequest $request) : Response | OrderDetailsResource | Application | ResponseFactory
        {
            try {
                return new OrderDetailsResource( $this->orderService->changePaymentStatus( $order , $request , FALSE ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function fullFill(Request $request )
        {
            $items = json_decode( $request->items , TRUE );
            foreach ( $items as $item ) {
                $order_product = OrderProduct::find( $item[ 'product_id' ] );
                $order_product->increment( 'quantity_picked' , $item[ 'picking_now' ] );
            }
        }
    }
