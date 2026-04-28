<?php

    namespace App\Http\Controllers;

    use App\Enums\State;
    use App\Models\Business;
    use App\Models\Order;
    use App\Models\Subscription;
    use App\Models\SubscriptionPlan;
    use App\Models\WhatsappUserSession;
    use App\Services\PdfExportService;
    use Exception;
    use Illuminate\Http\Client\ConnectionException;
    use Illuminate\Http\Request;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Validation\ValidationException;
    use Smartisan\Settings\Facades\Settings;

    class WhatsAppController extends Controller
    {
        /**
         * @var IotecController $iotec
         *     https://developers.facebook.com/documentation/business-messaging/whatsapp
         */
        private IotecController $iotec;
        private Request         $request;

        private string $phoneNumberId;
        private string $accessToken;

        public function __construct()
        {
            $this->iotec = new IotecController();
//            $this->phoneNumberId = config( 'app.whatsapp_phone_number_id' );
            $this->accessToken = config( 'whatsapp.whatsapp_access_token' );
        }

        public function index(Request $request)
        {
            $hub_challenge    = $request->input( 'hub_challenge' );
            $hub_verify_token = $request->input( 'hub_verify_token' );
            if ( $hub_verify_token == '7c3fd7fa-f580-4e3e-9a1f-92ab227261cb' ) {
                return $hub_challenge;
            }
            return NULL;
        }

        /**
         * @throws ConnectionException
         */
        public function message(Request $request)
        {
            $phone   = Arr::get( $request , 'entry.0.changes.0.value.messages.0.from' );
            $message = Arr::get( $request , 'entry.0.changes.0.value.messages.0.text.body' );
            if ( $phone && $message ) {
                $session = WhatsappUserSession::where( 'phone_number' , $phone )->first();
                if ( ! $session ) {
                    $session = WhatsappUserSession::create(
                        [
                            'phone_number' => $phone ,
                            'state'        => 'welcome' , 'data' => []
                        ]
                    );
                }
                $this->handleMessage( strtolower( trim( $message ) ) , $session );
            }
            return response()->json();
        }

        /**
         * @throws ConnectionException
         */
        public function handleMessage(string $text , WhatsappUserSession $session)
        {
            $state = $session->state;
            $data  = $session->data ?? [];

            if ( $text === 'hi' ) {
                $session->update( [ 'state' => 'main_menu' , 'data' => [] ] );
                return $this->showMainMenu( $session );
            }

            switch ( $state ) {
                case 'main_menu':
                    return $this->handleMainMenuSelection( $text , $session );

                case State::AWAITING_BUSINESS_ID_RENEW:
                    $business_id           = $text;
                    $data[ 'business_id' ] = $business_id;
                    $subscription          = Subscription::where( 'business_id' , $business_id )->latest()->first();
                    $business              = Business::where( 'business_id' , $business_id )->first();
                    if ( ! $business ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Business ID. Try again.' );
                    }
                    $business_name     = $business->business_name;
                    $plan              = $subscription->plan;
                    $data[ 'amount' ]  = ( $plan->amount ) * 1.04;
                    $data[ 'plan_id' ] = $plan->id;
                    $formatted_amount  = number_format( $data[ 'amount' ] );
                    $session->update( [ 'state' => State::AWAITING_PAYMENT_PHONE_RENEW , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , "Renewal total cost including charges $formatted_amount. Enter phone number to pay for $business_name." );

                case State::AWAITING_PAYMENT_PHONE_RENEW:
                    $phone = substr( $text , -9 );
                    if ( strlen( $phone ) < 9 ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Phone number. Try again.' );
                    }
                    $data[ 'phone_to_pay' ] = $phone;
                    $payload                = [ 'total' => $data[ 'amount' ] , 'phone' => $phone , 'business_id' => $data[ 'business_id' ] , 'plan_id' => $data[ 'plan_id' ] ];
                    $this->iotec->pay( ( $this->request->merge( $payload ) ) );
                    $session->update( [ 'state' => 'done' , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , 'Thanks! Enter mobile money PIN in prompt to confirm payment.' );

                case State::AWAITING_PLAN_ADD:
                    $plans      = SubscriptionPlan::all();
                    $plan_index = (int) $text - 1;
                    if ( ! isset( $plans[ $plan_index ] ) ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid selection. Try again.' );
                    }
                    $selectedPlan      = $plans[ $plan_index ];
                    $data[ 'plan_id' ] = $selectedPlan[ 'id' ];
                    $amount            = $selectedPlan[ 'amount' ] * 1.04;
                    $data[ 'amount' ]  = $amount;
                    $session->update( [ 'state' => State::AWAITING_BUSINESS_ID_ADD , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , 'Enter your Business ID' );

                case State::AWAITING_BUSINESS_ID_ADD:
                    $business_id           = $text;
                    $data[ 'business_id' ] = $business_id;
                    $business              = Business::where( 'business_id' , $business_id )->first();
                    if ( ! $business ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Business ID. Try again.' );
                    }
                    $business_name    = $business->business_name;
                    $formatted_amount = number_format( $data[ 'amount' ] );
                    $session->update( [ 'state' => State::AWAITING_PAYMENT_PHONE_RENEW , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , "Subscription total cost including charges $formatted_amount. Enter phone number to pay $business_name" );

                case State::AWAITING_PAYMENT_PHONE_ADD:
                    $phone = substr( $text , -9 );
                    if ( strlen( $phone ) < 9 ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Phone number. Try again.' );
                    }
                    $data[ 'phone_to_pay' ] = $phone;
                    $payload                = [ 'total' => $data[ 'amount' ] , 'phone' => $phone , 'business_id' => $data[ 'business_id' ] , 'plan_id' => $data[ 'plan_id' ] ];
                    $this->iotec->pay( ( $this->request->merge( $payload ) ) );
                    $session->update( [ 'state' => 'done' , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , 'Thanks! Your subscription will be added.' );

                case State::AWAITING_BUSINESS_ID_UPGRADE:
                    $business_id           = $text;
                    $data[ 'business_id' ] = $business_id;
                    $business              = Business::where( 'business_id' , $business_id )->first();
                    if ( ! $business ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Business ID. Try again.' );
                    }
                    $business_name             = $business->business_name;
                    $available                 = SubscriptionPlan::all();
                    $data[ 'upgrade_options' ] = $available->all();
                    $data[ 'business_name' ]   = $business_name;
                    $session->update( [ 'state' => State::AWAITING_PLAN_UPGRADE , 'data' => $data ] );
                    $list = $available->map( fn($p , $i) => ( $i + 1 ) . ". {$p['name']} - {$p['amount']}" )->implode( "\n" );
                    return $this->sendTextMessage( $session->phone_number , "Select a plan to upgrade:\n" . $list );

                case State::AWAITING_PLAN_UPGRADE:
                    $index   = (int) $text - 1;
                    $options = $data[ 'upgrade_options' ];
                    if ( ! isset( $options[ $index ] ) ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid selection. Try again.' );
                    }
                    $plan              = $options[ $index ];
                    $data[ 'plan_id' ] = $plan[ 'id' ];
                    $amount            = $plan[ 'amount' ] * 1.04;
                    $business_name     = $data[ 'business_name' ];
                    $data[ 'amount' ]  = $amount;
                    $session->update( [ 'state' => State::AWAITING_PAYMENT_PHONE_UPGRADE , 'data' => $data ] );
                    $formatted_amount = number_format( $amount );
                    return $this->sendTextMessage( $session->phone_number , "Upgrade total costs including charges $formatted_amount. Enter phone number to pay $business_name." );

                case State::AWAITING_PAYMENT_PHONE_UPGRADE:
                    $phone = substr( $text , -9 );
                    if ( strlen( $phone ) < 9 ) {
                        return $this->sendTextMessage( $session->phone_number , 'Invalid Phone number. Try again.' );
                    }
                    $data[ 'phone_to_pay' ] = $phone;
                    $session->update( [ 'state' => 'done' , 'data' => $data ] );
                    $payload = [ 'total' => $data[ 'amount' ] , 'phone' => $phone , 'business_id' => $data[ 'business_id' ] , 'plan_id' => $data[ 'plan_id' ] ];
                    $this->iotec->pay( ( $this->request->merge( $payload ) ) );
                    $session->update( [ 'state' => 'done' , 'data' => $data ] );
                    return $this->sendTextMessage( $session->phone_number , 'Thanks! Your upgrade will be processed.' );

                default:
                    return $this->sendTextMessage( $session->phone_number , 'Unrecognized command. Type Hi to start over.' );
            }
        }

        /**
         * @throws ConnectionException
         */
        protected function showMainMenu(WhatsappUserSession $session)
        {
            $menu = config( 'whatsapp.main_menu' );
            $text = $menu[ 'message' ] . "\n" . collect( $menu[ 'options' ] )
                    ->map( fn($desc , $key) => "$key. $desc" )
                    ->implode( "\n" );
            return $this->sendTextMessage( $session->phone_number , $text );
        }

        private function sendTemplate(string $to , string $templateName , array $components)
        {
            $token           = config( 'whatsapp.whatsapp_access_token' );
            $phone_number_id = config( 'app.whatsapp_phone_number_id' );

            $data = [
                'messaging_product' => 'whatsapp' ,
                'to'                => $to ,
                'type'              => 'template' ,
                'template'          => [
                    'name'       => $templateName ,
                    'language'   => [ 'code' => 'en_UG' ] ,
                    'components' => $components ,
                ] ,
            ];

            return Http::withToken( $token )
                       ->withHeaders( [ 'Content-Type' => 'application/json' ] )
                       ->post( "https://graph.facebook.com/v25.0/$phone_number_id/messages" , $data );
        }

        function sendTemplateMessage(Request $request)
        {
            try {
                $to       = $request->input( 'to' );
                $template = $request->input( 'template' );

                // Use the provided components array, or fallback to the old 'body' wrap for backward compatibility
                $components = $request->input( 'components' , [
                    [
                        'type'       => 'body' ,
                        'parameters' => $request->input( 'parameters' , [] )
                    ]
                ] );

                $data = [
                    'messaging_product' => 'whatsapp' ,
                    'to'                => $to ,
                    'type'              => 'template' ,
                    'template'          => [
                        'name'       => $template ,
                        'language'   => [
                            'code' => 'en_UG'
                        ] ,
                        'components' => $components
                    ]
                ];
                return $this->send( $data );
            } catch ( ConnectionException $e ) {
                info( $e->getMessage() );
            }
        }

        protected function handleMainMenuSelection(string $text , WhatsappUserSession $session)
        {
            switch ( $text ) {
                case '1':
                    $session->update( [ 'state' => State::AWAITING_BUSINESS_ID_RENEW , 'data' => [] ] );
                    return $this->sendTextMessage( $session->phone_number , 'Enter your Business ID' );

                case '2':
                    $plans = SubscriptionPlan::all();
                    $session->update( [ 'state' => State::AWAITING_PLAN_ADD , 'data' => [] ] );
                    $planList = $plans->map( fn($p , $i) => ( $i + 1 ) . ". {$p['name']} - {$p['amount']}" )
                                      ->implode( "\n" );
                    return $this->sendTextMessage( $session->phone_number , "Select a subscription plan:\n" . $planList );

                case '3':
                    $session->update( [ 'state' => State::AWAITING_BUSINESS_ID_UPGRADE , 'data' => [] ] );
                    return $this->sendTextMessage( $session->phone_number , 'Enter your Business ID for upgrade:' );

                default:
                    return $this->sendTextMessage( $session->phone_number , 'Invalid option. Type Hi to restart.' );
            }
        }


        /**
         * @throws ConnectionException
         */
        function sendTextMessage($to , $message)
        {
            $data = [
                'messaging_product' => 'whatsapp' ,
                'to'                => $to ,
                'type'              => 'text' ,
                'text'              => [
                    'body' => $message ,
                ]
            ];
            return $this->send( $data );
        }


        /**
         * @throws ConnectionException
         */
        private function send(array $data)
        {
            $token           = config( 'whatsapp.whatsapp_access_token' );
            $phone_number_id = config( 'app.whatsapp_phone_number_id' );
            return Http::withToken( $token )
                       ->withHeaders( [
                           'Authorization' => "Bearer $token" ,
                           'Content-Type'  => 'application/json'
                       ] )->post( "https://graph.facebook.com/v25.0/$phone_number_id/messages" , $data );
        }

        /**
         * @throws ConnectionException
         */
        public function sendMessage(Request $request)
        {
            $this->sendTextMessage( $request->input( 'phone' ) , $request->input( 'message' ) );
        }

        public function sendOrderPdf(Order $order , PdfExportService $pdfExportService)
        {
            try {
                $phone           = $order->user->phone;
                $pdfBytes        = $pdfExportService->pdfContent( $order )->pdf();
                $label           = orderLabel( $order );
                $order_serial_no = $order->order_serial_no;
                $filename        = $order->user->name . ' ' . $label . '#' . $order_serial_no . '.pdf';
                $mediaId         = $this->uploadMedia( $pdfBytes , $filename );
                $send            = $this->sendDocument( $phone , $mediaId , $filename );
                info( $send );
            } catch ( Exception $e ) {
                info( $e->getMessage() );
            }
        }

        private function uploadMedia(string $pdfBytes , string $filename)
        {
            try {
                $token           = config( 'whatsapp.whatsapp_access_token' );
                $phone_number_id = config( 'app.whatsapp_phone_number_id' );

                $response = Http::withToken( $token )
                                ->attach( 'file' , $pdfBytes , $filename , [ 'Content-Type' => 'application/pdf' ] )
                                ->post( "https://graph.facebook.com/v25.0/$phone_number_id/media" , [
                                    'messaging_product' => 'whatsapp' ,
                                    'type'              => 'application/pdf' ,
                                ] );

                info( $response );
                if ( $response->successful() ) {
                    return $response->json( 'id' );
                }
                info( 'WhatsApp media upload failed: ' . $response->body() );

            } catch ( Exception $e ) {
                info( $e->getMessage() );
            }
        }

        /**
         * Send a document message using an already-uploaded media ID.
         *
         * @throws ConnectionException
         */
        private function sendDocument(string $to , string $mediaId , string $filename)
        {
            $data = [
                'messaging_product' => 'whatsapp' ,
                'to'                => $to ,
                'type'              => 'document' ,
                'document'          => [
                    'id'       => $mediaId ,
                    'filename' => $filename ,
                    'caption'  => 'Please find your order document attached.' ,
                ] ,
            ];

            return $this->send( $data );
        }


        public function createQuotationTemplate()
        {
            $wabaId = config( 'whatsapp.whatsapp_business_id' );
            $token  = config( 'whatsapp.whatsapp_access_token' );

            $payload = [
                'name'       => 'quotation_invoice1' ,
                'language'   => 'en_UG' ,
                'category'   => 'UTILITY' ,
                'components' => [
                    [
                        'type'    => 'BODY' ,
                        'text'    => "Hi {{1}}, here is the quotation you requested from {{2}} 📄\n\n🧾 Quote No: {{3}}\n⏳ Valid Until: {{4}}\n💰 Total Amount: {{5}} \n\nPlease review the attached PDF for a detailed breakdown. If you are ready to proceed, tap the button below.\n\nPowered securely by Smart Duuka" ,
                        'example' => [
                            'body_text' => [
                                [
                                    'John Doe' ,        // client_name
                                    'Smart Duuka' ,     // business_name
                                    'QT-00123' ,        // quotation_number
                                    '30-06-2024' ,      // expiry_date
                                    'UGX 500,000' ,     // total_amount
                                ]
                            ]
                        ]
                    ] ,
                    [
                        'type'    => 'BUTTONS' ,
                        'buttons' => [
                            [
                                'type'    => 'URL' ,
                                'text'    => 'Review & Accept' ,
                                'url'     => 'https://api.smartduuka.com/q/{{1}}' ,
                                'example' => [
                                    'demoshop/132'
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withToken( $token )
                            ->withHeaders( [ 'Content-Type' => 'application/json' ] )
                            ->post( "https://graph.facebook.com/v25.0/$wabaId/message_templates" , $payload );

            return $response->json();
        }

        public function sendQuotationNotification(Order $quotation , string $tenant)
        {
            $dynamicSuffix = "{$tenant}/{$quotation->id}";

            $components = [
                [
                    'type'       => 'body' ,
                    'parameters' => [
                        [ 'type' => 'text' , 'text' => (string) $quotation->user->name ] ,
                        [ 'type' => 'text' , 'text' => (string) company()[ 'company_name' ] ] ,
                        [ 'type' => 'text' , 'text' => (string) $quotation->order_serial_no ] ,
                        [ 'type' => 'text' , 'text' => datetime( $quotation->due_date ) ] ,
                        [ 'type' => 'text' , 'text' => currency( $quotation->total ) ] ,
                    ]
                ] ,
                [
                    'type'       => 'button' ,
                    'sub_type'   => 'url' ,
                    'index'      => 0 ,
                    'parameters' => [
                        [ 'type' => 'text' , 'text' => $dynamicSuffix ]
                    ]
                ]
            ];

            $re = $this->sendTemplate( $quotation->user->phone , 'quotation_invoice1' , $components );
            info( $re );
        }

        public function createDocumentTemplate()
        {
            // Note: You must add your WABA ID to your config/whatsapp.php file.
            // This is different from the phone_number_id.
            $wabaId = config( 'whatsapp.whatsapp_business_id' );
            $token  = config( 'whatsapp.whatsapp_access_token' );

            $payload = [
                'name'       => 'order_document_delivery' ,
                'language'   => 'en_UG' ,                   // Matching your system's language code
                'category'   => 'UTILITY' ,                 // UTILITY, MARKETING, or AUTHENTICATION
                'components' => [
                    // 1. The Header component specifying we will send a document
                    [
                        'type'   => 'HEADER' ,
                        'format' => 'DOCUMENT'
                    ] ,
                    // 2. The Body component with a dynamic variable for the user's name
                    [
                        'type' => 'BODY' ,
                        'text' => 'Hello {{1}}, please find your requested order document attached below.'
                    ]
                ]
            ];

            $response = Http::withToken( $token )
                            ->withHeaders( [
                                'Content-Type' => 'application/json'
                            ] )
                            ->post( "https://graph.facebook.com/v25.0/$wabaId/message_templates" , $payload );

            // Log the response to check for success or validation errors from Meta
            info( 'Template Creation Response: ' . $response->body() );

            return $response->json();
        }

        /**
         * @throws ValidationException
         * @throws ConnectionException
         */
        public function registerPhoneNumber(Request $request)
        {
            $full_phone_number = $request->string( 'phone' );
            $country_code      = substr( $full_phone_number , 1 , 3 );
            $national_number   = substr( $full_phone_number , 4 );

            $wabaId = config( 'whatsapp.whatsapp_business_id' );
            $token  = config( 'whatsapp.whatsapp_access_token' );

            // 1. Register the phone number
            $registerResponse = Http::withToken( $token )
                                    ->post( "https://graph.facebook.com/v25.0/{$wabaId}/phone_numbers" , [
                                        'cc'                   => $country_code ,
                                        'phone_number'         => $national_number ,
                                        'migrate_phone_number' => FALSE ,
                                        'verified_name'        => company()[ 'company_name' ] ,
                                    ] );

            if ( ! $registerResponse->successful() ) {
                return response()->json( [
                    'success' => FALSE ,
                    'message' => 'Failed to register phone number.' ,
                    'error'   => $registerResponse->json()
                ] , 400 );
            }
            info( $registerResponse->json() );

            $phoneNumberId = $registerResponse->json( 'id' ) ?? config( 'app.whatsapp_phone_number_id' );

            if ( $phoneNumberId ) {
                Settings::group( 'company' )->set( [ 'company_whatsapp_phone' => $full_phone_number ] );

                tenant()->update( [
                    'company_whatsapp_phone' => $full_phone_number
                ] );

            }

            // 2. Cache the ID for 15 minutes so the verify method can find it
            // Using auth()->id() ensures this doesn't conflict if multiple tenants do this at once
            $cacheKey = 'pending_wa_id_' . auth()->id();
            Cache::put( $cacheKey , $phoneNumberId , now()->addMinutes( 15 ) );

            // 3. Automatically request the SMS verification code
            $codeResponse = Http::withToken( $token )
                                ->post( "https://graph.facebook.com/v25.0/{$phoneNumberId}/request_code" , [
                                    'code_method' => 'SMS' ,
                                    'language'    => 'en_UG'
                                ] );
            info( $codeResponse->json() );

            if ( ! $codeResponse->successful() ) {
                return response()->json( [
                    'success' => FALSE ,
                    'message' => 'Phone registered, but failed to send SMS verification code.' ,
                    'error'   => $codeResponse->json()
                ] , 400 );
            }

            return response()->json( [
                'success' => TRUE ,
                'message' => 'Phone number registered and SMS code requested.' ,
            ] );
        }

        public function verifyOtp(Request $request)
        {
            $validated = $request->validate( [
                'otp' => 'required|string' ,
            ] );

            // Retrieve the cached ID (and remove it from the cache at the same time using pull)
            $cacheKey      = 'pending_wa_id_' . auth()->id();
            $phoneNumberId = Cache::pull( $cacheKey ) ?? config( 'app.whatsapp_phone_number_id' );

            $token = config( 'whatsapp.whatsapp_access_token' );

            $response = Http::withToken( $token )
                            ->post( "https://graph.facebook.com/v25.0/{$phoneNumberId}/verify_code" , [
                                'code' => $validated[ 'otp' ] ,
                            ] );

            info( $response->json() );

            if ( ! $response->successful() ) {
                return response()->json( [
                    'success' => FALSE ,
                    'message' => 'Verification failed.' ,
                    'error'   => $response->json()
                ] , 400 );
            }

            tenant()->update( [
                'whatsapp_phone_number_id' => $phoneNumberId
            ] );

            return response()->json( [
                'success' => TRUE ,
                'message' => 'Phone number verified successfully.' ,
                'data'    => $response->json()
            ] );
        }

        /**
         * @param Request $request
         *
         * @return \Illuminate\Http\JsonResponse
         * @throws ConnectionException
         *  https://developers.facebook.com/documentation/business-messaging/whatsapp/solution-providers/registering-phone-numbers#step-4--register-the-number
         */
        public function registerVerifiedNumber(Request $request)
        {
            $validated = $request->validate([
                'pin' => 'required|string|size:6',
            ]);

            // Retrieve the phone number ID from the tenant. Fallback to config if necessary.
            $phoneNumberId = tenant('whatsapp_phone_number_id') ?? config('app.whatsapp_phone_number_id');
            $token         = config('whatsapp.whatsapp_access_token');

            if (!$phoneNumberId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone number ID not found. Ensure the OTP was verified first.',
                ], 400);
            }

            $response = Http::withToken($token)
                            ->post("https://graph.facebook.com/v25.0/{$phoneNumberId}/register", [
                                'messaging_product' => 'whatsapp',
                                'pin'               => $validated['pin'],
                            ]);

            info('WhatsApp Number Registration Response: ' . $response->body());

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register the phone number.',
                    'error'   => $response->json()
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Phone number successfully registered and ready for messaging.',
                'data'    => $response->json()
            ]);
        }
    }
