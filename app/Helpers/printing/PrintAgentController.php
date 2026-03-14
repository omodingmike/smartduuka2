<?php

    namespace App\Helpers\printing;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;

    class PrintAgentController extends Controller
    {
        /**
         * Bulletproof Business ID Resolver.
         * Ensures React and the Agent always resolve to the exact same tenant ID.
         */
        private function resolveBusinessId(Request $request)
        {
            // 1. From React request body/query
//            if ($request->filled('business_id')) {
//                return $request->input('business_id');
//            }
//            // 2. From Agent request body
//            if ($request->filled('identifier')) {
//                return $request->input('identifier');
//            }
//            // 3. From Agent headers
//            if ($request->hasHeader('X-Agent-Identifier')) {
//                return $request->header('X-Agent-Identifier');
//            }
//            // 4. Fallback to Auth User
//            if ($request->user() && $request->user()->business_id) {
//                return $request->user()->business_id;
//            }
            // 5. Absolute fallback
            return config('app.business_id');
        }

        // 1. React tells Laravel to broadcast a scan request
        public function triggerScan(Request $request)
        {
            $businessId = $this->resolveBusinessId($request);

            // MUST NOT BE COMMENTED OUT: Clears old scans and tells React to wait
            Cache::put("printer_scan_{$businessId}", ['status' => 'pending'], now()->addMinutes(2));

            // Fire the WebSocket event to wake up the Agent
            event(new PrintAgentScanRequested($businessId));

            return response()->json(['success' => true, 'message' => 'Scan broadcast triggered']);
        }

        // 2. The Agent posts the scan results back to this endpoint
        public function reportPrinters(Request $request)
        {
            $businessId = $this->resolveBusinessId($request);

            // Save the printers to the correct tenant's cache
            Cache::put(
                "printer_scan_{$businessId}",
                ['status' => 'completed', 'printers' => $request->printers],
                now()->addMinutes(5)
            );

            return response()->json(['success' => true]);
        }

        // 3. React continuously polls this to get the latest scan results
        public function latestScan(Request $request)
        {
            $businessId = $this->resolveBusinessId($request);

            $data = Cache::get("printer_scan_{$businessId}");

            if (!$data) {
                return response()->json(['status' => 'pending']);
            }

            return response()->json($data);
        }

        // 4. React sends a print job
        public function print(Request $request)
        {
            $businessId = $this->resolveBusinessId($request);

            $payload = $request->only(['jobId', 'type', 'printerName', 'htmlContent', 'data']);

            // Send over WebSocket!
            event(new PrintJobDispatched($businessId, $payload));

            return response()->json(['success' => true, 'message' => 'Job sent to queue']);
        }

        // 5. React kicks the cash drawer
        public function openDrawer(Request $request)
        {
            $businessId = $this->resolveBusinessId($request);

            event(new PrintDrawerOpenRequested($businessId, $request->printerName));

            return response()->json(['success' => true]);
        }

        // 6. React checks if the Agent is online
        public function status(Request $request)
        {
            return response()->json(['is_online' => true]);
        }

        // 7. Update Agent Job Status (When agent completes or fails a job)
        public function updateJobStatus(Request $request)
        {
            return response()->json(['success' => true]);
        }
    }