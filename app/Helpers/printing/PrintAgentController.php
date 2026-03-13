<?php

    namespace App\Helpers\printing;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;

    class PrintAgentController extends Controller
    {
        /**
         * Get the Business ID from the currently authenticated user/token.
         * (Adjust this based on how your multi-tenant DB is structured)
         */
        private function getBusinessId(Request $request)
        {
            // Example: If using a standard user relationship
            return $request->user()->business_id;
        }

        // 1. React tells Laravel to broadcast a scan request
        public function triggerScan(Request $request)
        {
            $businessId = $this->getBusinessId( $request );

            // Clear any old scan data and set status to pending
            Cache::put( "printer_scan_{$businessId}" , [ 'status' => 'pending' ] , now()->addMinutes( 1 ) );

            // Fire the WebSocket event to wake up the Agent
            event( new PrintAgentScanRequested( $businessId ) );

            return response()->json( [ 'success' => TRUE , 'message' => 'Scan broadcast triggered' ] );
        }

        // 2. The Agent posts the scan results back to this endpoint
        public function reportPrinters(Request $request)
        {
            // The Agent passes the connectionValue (domain) in the X-Agent-Identifier header
            // For security, you should resolve this to the actual business_id using your DB.
            $domain = $request->header( 'X-Agent-Identifier' ) ?? $request->identifier;

            // Example logic: $businessId = Business::where('domain', $domain)->value('id');
            $businessId = $this->getBusinessId( $request ); // Assuming the Agent's Bearer token identifies the user/business

            // Save the printers to the cache for the React app to fetch
            Cache::put(
                "printer_scan_{$businessId}" ,
                [ 'status' => 'completed' , 'printers' => $request->printers ] ,
                now()->addMinutes( 5 )
            );

            return response()->json( [ 'success' => TRUE ] );
        }

        // 3. React continuously polls this to get the latest scan results
        public function latestScan(Request $request)
        {
            $businessId = $this->getBusinessId( $request );

            $data = Cache::get( "printer_scan_{$businessId}" , [ 'status' => 'pending' ] );

            return response()->json( $data );
        }

        // 4. React sends a print job
        public function print(Request $request)
        {
            $businessId = $this->getBusinessId( $request );

            $payload = $request->only( [ 'jobId' , 'type' , 'printerName' , 'htmlContent' , 'data' ] );

            // Send over WebSocket!
            event( new PrintJobDispatched( $businessId , $payload ) );

            return response()->json( [ 'success' => TRUE , 'message' => 'Job sent to queue' ] );
        }

        // 5. React kicks the cash drawer
        public function openDrawer(Request $request)
        {
            $businessId = $this->getBusinessId( $request );

            event( new PrintDrawerOpenRequested( $businessId , $request->printerName ) );

            return response()->json( [ 'success' => TRUE ] );
        }

        // 6. React checks if the Agent is online
        public function status(Request $request)
        {
            // To do true presence, you need a PresenceChannel.
            // For PrivateChannels, returning true allows the UI to proceed without blocking.
            return response()->json( [ 'is_online' => TRUE ] );
        }

        // 7. Update Agent Job Status (When agent completes or fails a job)
        public function updateJobStatus(Request $request)
        {
            // Here you would log the successful print job to your database
            // e.g., PrintJob::where('job_id', $request->jobId)->update(['status' => $request->status]);

            return response()->json( [ 'success' => TRUE ] );
        }
    }