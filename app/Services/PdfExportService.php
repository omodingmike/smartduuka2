<?php

    namespace App\Services;

    use App\Enums\QuotationType;
    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\ThemeSetting;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Response;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Browsershot\Browsershot;

    class PdfExportService
    {
        public function exportOrder(Order $order , bool $stream = FALSE) : Response | ResponseFactory
        {
            try {

                $pdfContent = $this->pdfContent( $order );

                $order_serial_no = $order->order_serial_no;
                $label           = orderLabel( $order );
                $name            = $order->user->name . ' ' . $label . '#' . $order_serial_no;


                if ( $stream ) {
                    return response( $pdfContent->pdf() , 200 , [
                        'Content-Type'        => 'application/pdf' ,
                        'Content-Disposition' => "inline; filename=$name.pdf" ,
                    ] );
                }

                return response( $pdfContent->pdf() , 200 , [
                    'Content-Type'        => 'application/pdf' ,
                    'Content-Disposition' => "attachment; filename='$name.pdf'" ,
                ] );

            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function pdfContent(Order $order) : Browsershot
        {
            $html = $this->renderHtml( $order );

            $data = Browsershot::html( $html )
                               ->showBackground()
                               ->format( 'A4' )
                               ->scale( 0.95 )
                               ->margins( 0 , 0 , 0 , 0 )
                               ->noSandbox()
                               ->addChromiumArguments( [
                                   'no-sandbox' ,
                                   'disable-setuid-sandbox' ,
                                   'disable-dev-shm-usage' ,
                               ] );
            if ( config( 'app.chrome_path' ) ) {
                $data->setChromePath( config( 'app.chrome_path' ) );
            }
            return $data;
        }

        public function renderHtml(Order $order) : string | null
        {
            try {
                $colors = [ 'primaryColor' , 'primaryLight' , 'secondaryColor' , 'secondaryLight' ];

                $relations = [
                    'creditDepositPurchases.paymentMethod' ,
                    'user.addresses' ,
                    'stocks' ,
                    'user' ,
                    'creator' ,
                    'paymentMethods.paymentMethod'
                ];

                if ( $order->quotation_type === QuotationType::SERVICE ) {
                    $relations[] = 'orderServiceProducts.service';
                    $view        = 'quotations.service-quotation';
                }
                else if ( $order->quotation_type === QuotationType::COMBINED ) {
                    $relations[] = 'orderServiceProducts.service';
                    $relations[] = 'orderProducts.item';
                    $relations[] = 'orderProducts.product.taxes.tax';
                    $relations[] = 'orderProducts.product.unit:id,code';
                    $relations[] = 'orderProducts.product.sellingUnits:id,code';
                    $view        = 'quotations.combined-quotation';
                }
                else {
                    $relations[] = 'orderProducts.item';
                    $relations[] = 'orderProducts.product.taxes.tax';
                    $relations[] = 'orderProducts.product.unit:id,code';
                    $relations[] = 'orderProducts.product.sellingUnits:id,code';
                    $view        = 'quotations.quotation';
                }

                $order->load( $relations );

                $methods = $order->creditDepositPurchases->map( function ($purchase) {
                    return $purchase->paymentMethod;
                } )->unique();

                if ( $order->payment_method ) {
                    $data[]         = $order->paymentMethod;
                    $paymentMethods = $data;
                }
                else if ( count( $methods ) > 0 ) {
                    $paymentMethods = $methods;
                }
                else {
                    $paymentMethods = PaymentMethod::all();
                }
                $data = [
                    'order'          => $order ,
                    'balance'        => ( $order->total - $order->paid ) < 0 ? 0 : $order->total - $order->paid ,
                    'label'          => orderLabel( $order ) ,
                    'paymentMethods' => $paymentMethods ,
                    'company'        => (object) Settings::group( 'company' )->all() ,
                    'logo'           => ThemeSetting::where( [ 'key' => 'theme_logo' ] )->first()->logo ,
                ];
                foreach ( $colors as $color ) {
                    $data[ $color ] = setting( $color );
                }
                return view( $view , $data )->render();
            } catch ( Exception $exception ) {
                info( $exception->getMessage() );
                return NULL;
            } catch ( \Throwable $e ) {
                info( $e->getMessage() );
                return NULL;
            }
        }
    }
