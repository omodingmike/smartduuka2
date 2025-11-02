<?php

    namespace App\Services;

    use App\Models\Order;
    use App\Models\PaymentMethod;
    use App\Models\ThemeSetting;
    use Exception;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Browsershot\Browsershot;

    class PdfExportService
    {
        public function exportOrder(Request $request , bool $stream = false)
        {
            try {
                $html = $this->renderHtml($request);

                $pdfContent      = Browsershot::html($html)
                                              ->showBackground()
                                              ->format('A4');
                $order           = Order::find($request->id);
                $order_serial_no = $order->order_serial_no;
                $label           = orderLabel($order);
                $name            = $order->user->name . ' ' . $label . '#' . $order_serial_no;

                if ( ! config('app.dev') ) {
                    $pdfContent->setChromePath(config('app.chrome_path'));
                }

                if ( $stream ) {
                    return response($pdfContent->pdf() , 200 , [
                        'Content-Type'        => 'application/pdf' ,
                        'Content-Disposition' => "inline; filename=$name.pdf" ,
                    ]);
                }

                return response($pdfContent->pdf() , 200 , [
                    'Content-Type'        => 'application/pdf' ,
                    'Content-Disposition' => "attachment; filename='$name.pdf'" ,
                ]);

            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function renderHtml(Request $request) : string | null
        {
            try {
                $colors  = [ 'primaryColor' , 'primaryLight' , 'secondaryColor' , 'secondaryLight' ];
                $order   = Order::find($request->id)->load([ 'orderProducts.unit' , 'paymentMethod' , 'creditDepositPurchases.paymentMethod' , 'orderProducts.product.taxes.tax' , 'orderProducts.product.unit:id,code' , 'orderProducts.product.sellingUnits:id,code' , 'user.addresses' , 'stocks' ]);
                $methods = $order->creditDepositPurchases->map(function ($purchase) {
                    return $purchase->paymentMethod;
                })->unique();
                if ( $order->payment_method ) {
                    $data[]         = $order->paymentMethod;
                    $paymentMethods = $data;
                } else if ( count($methods) > 0 ) {
                    $paymentMethods = $methods;
                } else {
                    $paymentMethods = PaymentMethod::all();
                }
                $data = [
                    'order'          => $order ,
                    'balance'        => ( $order->total - $order->paid ) < 0 ? 0 : $order->total - $order->paid ,
                    'label'          => orderLabel($order) ,
//                    'paymentMethods' => $order->payment_status == PaymentStatus::PAID ? $methods : PaymentMethod::all() ,
                    'paymentMethods' => $paymentMethods ,
                    'company'        => (object) Settings::group('company')->all() ,
                    'logo'           => ThemeSetting::where([ 'key' => 'theme_logo' ])->first()->logo ,
                ];
                foreach ( $colors as $color ) {
                    $data[$color] = settingValue($color);
                }
                return view('quotations.quotation' , $data)->render();
            } catch ( Exception $exception ) {
                info($exception->getMessage());
                return null;
            } catch ( \Throwable $e ) {
                info($e->getMessage());
                return null;
            }
        }
    }
