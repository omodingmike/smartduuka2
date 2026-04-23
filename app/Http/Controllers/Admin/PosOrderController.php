<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\PreOrderStatus;
    use App\Enums\SettingsEnum;
    use App\Enums\StockStatus;
    use App\Exports\OrderExport;
    use App\Http\Requests\OrderStatusRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\PaymentStatusRequest;
    use App\Http\Resources\OrderDetailsResource;
    use App\Http\Resources\OrderResource;
    use App\Jobs\SendInvoiceMailJob;
    use App\Models\Order;
    use App\Models\PaymentMethodTransaction;
    use App\Models\PosPayment;
    use App\Models\RetailPrice;
    use App\Models\Stock;
    use App\Models\WholeSalePrice;
    use App\Services\OrderService;
    use App\Services\PdfExportService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Log;
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
//            $this->middleware( [ 'permission:pos-orders' ] )->only( 'index' , 'show' , 'destroy' , 'export' , 'changeStatus' , 'changePaymentStatus' );
        }

        public function index(Request $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return $this->orderService->list( $request );
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

        public function show(Order $order)
        {
            try {
//                return new OrderDetailsResource( $this->orderService->show( $order , FALSE ) );
                return new OrderResource( $this->orderService->show( $order ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function pdf(Order $order)
        {
            $pdfExportService = new PdfExportService();
            return $pdfExportService->exportOrder( $order , TRUE );
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

        public function exportOrder(Order $order)
        {
            $pdfExportService = new PdfExportService();
            return $pdfExportService->exportOrder( $order );
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

        public function mailQuotation(Order $order)
        {
            try {
                $pdfExportService = new PdfExportService();
                $html             = $pdfExportService->renderHtml( $order );
                $this->storePDF( $html , orderName( $order ) );
                SendInvoiceMailJob::dispatch( $order );
                return response()->json();
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

        public function fullFill(Request $request)
        {
//            return 1;
            try {
                DB::transaction( function () use ($request) {
                    $proratedItems = json_decode( $request->proratedItems , TRUE );
                    $extraItems    = json_decode( $request->extraItems , TRUE );
                    $action        = $request->string( 'action' );
                    $topUpAmount   = $request->integer( 'topUpAmount' );
                    $difference    = $request->integer( 'difference' );
                    $paymentMethod = $request->integer( 'paymentMethod' );
                    $order         = Order::find( $request->integer( 'order_id' ) );
                    $order->update( [
                        'pre_order_status' => PreOrderStatus::FULFILLED
                    ] );
                    $new_order_total = 0;

                    if ( $action == 'HONOR' ) {
                        foreach ( $order->orderProducts as $order_product ) {
                            Stock::create( [
                                'product_id'   => $order_product->item_id ,
                                'item_id'      => $order_product->item_id ,
                                'model_id'     => $order_product->item_id ,
                                'item_type'    => $order_product->item_type ,
                                'model_type'   => $order_product->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id ,
                                'quantity'     => -$order_product->quantity ,
                            ] );
                        }
                    }
                    if ( $action == 'PRORATE' ) {
                        foreach ( $proratedItems as $prorated_item ) {
                            $order->orderProducts()
                                  ->where( 'id' , $prorated_item[ 'orderProductId' ] )
                                  ->update( [ 'quantity' => $prorated_item[ 'newQty' ] ] );
                        }

                        foreach ( $order->orderProducts as $order_product ) {
                            $price         = $order_product->price;
                            $current_price = 0;

                            if ( $price instanceof RetailPrice ) {
                                $current_price = $price->selling_price;
                            }

                            if ( $price instanceof WholeSalePrice ) {
                                $current_price = $price->price;
                            }

                            $new_total = $current_price * $order_product->quantity;

                            $order_product->update( [
                                'unit_price' => $current_price ,
                                'total'      => $new_total ,
                            ] );

                            $new_order_total += $new_total;

                            Stock::create( [
                                'product_id'   => $order_product->item_id ,
                                'item_id'      => $order_product->item_id ,
                                'model_id'     => $order_product->item_id ,
                                'item_type'    => $order_product->item_type ,
                                'model_type'   => $order_product->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id ,
                                'quantity'     => -$order_product->quantity ,
                            ] );
                        }
                        $order->update( [ 'total' => $new_order_total ] );
                    }

                    if ( $action == 'GIVE_MORE_AMOUNT' ) {
                        foreach ( $extraItems as $extraItem ) {
                            $order->orderProducts()
                                  ->where( 'id' , $extraItem[ 'orderProductId' ] )
                                  ->update( [ 'quantity' => $extraItem[ 'newQty' ] ] );
                        }

                        foreach ( $order->orderProducts as $order_product ) {
                            $price         = $order_product->price;
                            $current_price = 0;

                            if ( $price instanceof RetailPrice ) {
                                $current_price = $price->selling_price;
                            }

                            if ( $price instanceof WholeSalePrice ) {
                                $current_price = $price->price;
                            }

                            $new_total = $current_price * $order_product->quantity;

                            $order_product->update( [
                                'unit_price' => $current_price ,
                                'total'      => $new_total ,
                            ] );

                            $new_order_total += $new_total;

                            Stock::create( [
                                'product_id'   => $order_product->item_id ,
                                'item_id'      => $order_product->item_id ,
                                'model_id'     => $order_product->item_id ,
                                'item_type'    => $order_product->item_type ,
                                'model_type'   => $order_product->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id ,
                                'quantity'     => -$order_product->quantity ,
                            ] );
                        }
                        $order->update( [ 'total' => $new_order_total ] );
                    }


                    if ( $action == 'TOP_UP' ) {
                        $order->increment( 'paid' , $topUpAmount );

                        foreach ( $order->orderProducts as $order_product ) {
                            $price         = $order_product->price;
                            $current_price = 0;
                            if ( $price instanceof RetailPrice ) {
                                $current_price = $price->selling_price;
                            }
                            if ( $price instanceof WholeSalePrice ) {
                                $current_price = $price->price;
                            }
                            $new_total = $current_price * $order_product->quantity;
                            $order_product->update( [
                                'unit_price' => $current_price ,
                                'total'      => $new_total ,
                            ] );
                            $new_order_total += $new_total;
                            Stock::create( [
                                'product_id'   => $order_product->item_id ,
                                'item_id'      => $order_product->item_id ,
                                'model_id'     => $order_product->item_id ,
                                'item_type'    => $order_product->item_type ,
                                'model_type'   => $order_product->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id ,
                                'quantity'     => -$order_product->quantity ,
                            ] );
                        }
                        PosPayment::create( [
                            'order_id'          => $order->id ,
                            'date'              => now() ,
                            'reference_no'      => time() ,
                            'amount'            => $difference ,
                            'payment_method_id' => $paymentMethod ,
                            'register_id'       => register()->id
                        ] );

                        PaymentMethodTransaction::create( [
                            'amount'            => $difference ,
                            'item_type'         => Order::class ,
                            'item_id'           => $order->id ,
                            'charge'            => 0 ,
                            'description'       => 'Order Payment #' . $order->order_serial_no ,
                            'payment_method_id' => $paymentMethod ,
                        ] );
                        $order->update( [ 'total' => $new_order_total ] );
                    }

                    if ( $action == 'REFUND_BALANCE' ) {
                        $refundAmount = $request->integer( 'refundAmount' );
                        $refundMethod = $request->integer( 'refundMethod' );
                        $order->decrement( 'paid' , $refundAmount );

                        foreach ( $order->orderProducts as $order_product ) {
                            $price         = $order_product->price;
                            $current_price = 0;
                            if ( $price instanceof RetailPrice ) {
                                $current_price = $price->selling_price;
                            }
                            if ( $price instanceof WholeSalePrice ) {
                                $current_price = $price->price;
                            }
                            $new_total = $current_price * $order_product->quantity;
                            $order_product->update( [
                                'unit_price' => $current_price ,
                                'total'      => $new_total ,
                            ] );
                            $new_order_total += $new_total;
                            Stock::create( [
                                'product_id'   => $order_product->item_id ,
                                'item_id'      => $order_product->item_id ,
                                'model_id'     => $order_product->item_id ,
                                'item_type'    => $order_product->item_type ,
                                'model_type'   => $order_product->item_type ,
                                'status'       => StockStatus::RECEIVED ,
                                'warehouse_id' => $order->warehouse_id ,
                                'quantity'     => -$order_product->quantity ,
                            ] );
                        }

                        PosPayment::create( [
                            'order_id'          => $order->id ,
                            'date'              => now() ,
                            'reference_no'      => time() ,
                            'amount'            => -$refundAmount ,
                            'payment_method_id' => $refundMethod ,
                            'register_id'       => register()->id
                        ] );

                        PaymentMethodTransaction::create( [
                            'amount'            => $refundAmount ,
                            'item_type'         => Order::class ,
                            'item_id'           => $order->id ,
                            'charge'            => 0 ,
                            'description'       => 'Order Payment #' . $order->order_serial_no ,
                            'payment_method_id' => $refundMethod ,
                        ] );
                        $order->update( [ 'total' => $new_order_total ] );
                    }
                } );
            } catch ( Exception $e ) {
                throw new Exception( $e->getMessage() );
            }
        }

        public function preOrderRefund(Order $order , Request $request)
        {
            try {
                return DB::transaction( function () use ($order) {
                    // Update order statuses to reflect the refund/cancellation.
                    $order->update( [
                        'pre_order_status' => PreOrderStatus::REFUNDED ,
                    ] );

//                    $order->paymentMethodTransactions()->delete();
//                    $order->posPayments()->delete();

                    // Release any reserved stock.
                    // This assumes that creating a pre-order increments the 'quantity_ordered' on the stock record.

//                    foreach ($order->orderProducts as $orderProduct) {
//                        $stock = Stock::where([
//                            'item_id'      => $orderProduct->item_id,
//                            'item_type'    => $orderProduct->item_type,
//                            'warehouse_id' => $order->warehouse_id,
//                        ])->first();
//
//                        if ($stock && $stock->quantity_ordered >= $orderProduct->quantity) {
//                            $stock->decrement('quantity_ordered', $orderProduct->quantity);
//                        }
//                    }

                    activityLog( "Refunded Pre-Order: {$order->order_serial_no}" );
                    return response()->json();
                } );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
