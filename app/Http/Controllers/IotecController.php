<?php

    namespace App\Http\Controllers;

    use App\Http\Resources\SubscriptionResource;
    use App\Models\Subscription;
    use Exception;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Str;

    class IotecController extends Controller
    {
        public function pay(Request $request)
        {
            try {
                $amount         = $request->input( 'total' );
                $phone          = $request->input( 'phone' );
                $business_id    = $request->input( 'business_id' );
                $project_id     = $request->input( 'project_id' );
                $phone          = "256$phone";
                $transaction_id = Str::uuid()->getHex();
                $data           = [
                    'externalId' => "$transaction_id" ,
                    'payer'      => $phone ,
                    'amount'     => $amount ,
                    'payeeNote'  => 'Smartduuka' ,
                    'walletId'   => config( 'system.iotec_wallet_id' ) ,
                ];

                $response = Http::withToken( token: $this->getAccessToken() )
                                ->post( url: 'https://pay.iotec.io/api/collections/collect' , data: $data );

                if ( isset( $response[ 'status' ] ) && strtolower( $response[ 'status' ] ) == 'pending' ) {
                    $active       = Subscription::where( 'expires_at' , '>=' , now() )
                                                ->where( 'status' , 'active' )
                                                ->where( 'project_id' , $project_id )
                                                ->latest()->first();
                    $subscription = Subscription::create( [
                        'plan_id'        => $request->input( 'id' ) ,
                        'invoice_no'     => $transaction_id ,
                        'project_id'     => $project_id ,
                        'business_id'    => $business_id ,
                        'phone'          => $phone ,
                        'vendor_message' => $response[ 'statusMessage' ] ,
                        'amount'         => $amount ,
                        'external_id'    => $transaction_id ,
                        'starts_at'      => $active ? $active->expires_at : now() ,
                        'expires_at'     => $active ? $active->expires_at->addMonths( $request->input( 'months' ) ) : now()->addMonths( $request->input( 'months' ) ) ,
                    ] );
                    $subscription->update( [ 'invoice_no' => date( 'dmy' ) . $subscription->id ] );

                    $subscription = $subscription->load( 'plan' );
                    return new SubscriptionResource( $subscription );
                }
                else {
                    return response()->json( [ 'message' => 'Failed to process payment' ] );
                }
            } catch ( Exception $e ) {
                info( $e->getMessage() );
                return response()->json( [ 'message' => 'Failed to process payment' ] , 408 );
            }
        }

        public function getAccessToken() : string
        {
            $response = Http::asForm()->post(
                url: 'https://id.iotec.io/connect/token' ,
                data: [
                    'client_id'     => config( 'system.iotec_client_id' ) ,
                    'client_secret' => config( 'system.iotec_secrete' ) ,
                    'grant_type'    => 'client_credentials' ,
                ] );
            return $response[ 'access_token' ];
        }

        public function success(Request $request) : JsonResponse
        {
            try {
                if ( $request->has( 'status' ) && strtolower( $request->input( 'status' ) ) == 'success' ) {
                    $subscription = Subscription::where( [ 'external_id' => $request->input( 'externalId' ) ] )->first();
                    if ( $subscription ) {
                        $subscription->vendor_transaction_id = $request->input( 'vendorTransactionId' );
                        $subscription->payment_status        = 'success';
                        $subscription->status                = 'active';
                        $subscription->vendor_message        = $request->input( 'statusMessage' );
                        $subscription->save();
                    }
                }
                if ( $request->has( 'status' ) && strtolower( $request->input( 'status' ) ) == 'Failed' ) {
                    $status_message = $request->input( 'statusMessage' );
                    $subscription   = Subscription::where( [ 'external_id' => $request->input( 'externalId' ) ] )->first();
                    if ( $subscription ) {
                        $subscription->vendor_transaction_id = $request->input( 'vendorTransactionId' );
                        $subscription->payment_status        = 'failed';
                        $subscription->vendor_message        = $status_message;
                        $subscription->save();
                    }
                }
            } catch ( Exception $e ) {
                info( $e->getMessage() );
            }
            return response()->json();
        }
    }
