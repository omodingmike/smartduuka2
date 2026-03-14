<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
// Make sure to create these events if you haven't already!
use App\Events\PrintAgentScanRequested;
use App\Events\PrintJobDispatched;
use App\Events\PrintDrawerOpenRequested;

class PrintAgentController extends Controller
{
    /**
     * Bulletproof Business ID Resolver.
     * Ensures React and the Agent always resolve to the exact same tenant ID.
     */
    private function resolveBusinessId(Request $request)
    {
        // 1. From React request body/query
        if ($request->filled('business_id')) {
            return $request->input('business_id');
        }
        
        // 2. From Agent request body (status updates)
        if ($request->filled('identifier')) {
            return $request->input('identifier');
        }
        
        // 3. From Agent headers (Auth checks)
        if ($request->hasHeader('X-Agent-Identifier')) {
            return $request->header('X-Agent-Identifier');
        }
        
        // 4. Fallback to Authenticated User (React Frontend Context)
        if (auth()->check() && auth()->user()->business_id) {
            return auth()->user()->business_id;
        }

        // 5. Hard Fail - Prevent printing to wrong tenant
        abort(403, 'Could not resolve Business ID for Print Agent context.');
    }

    /**
     * Called by the Electron Agent on startup to verify Auth and get Business ID
     */
    public function me(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);

        return response()->json([
            'business_id' => $businessId,
            'status' => 'active',
            'message' => 'Agent authorized successfully.'
        ]);
    }

    // 1. React tells Laravel to broadcast a scan request
    public function triggerScan(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);

        // Clears old scans and tells React to wait
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
            ['status' => 'completed', 'printers' => $request->input('printers', [])],
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

        $request->validate([
            'type' => 'required|in:html,raw',
            'printerName' => 'required|string',
        ]);

        // Construct the exact payload expected by Electron
        $payload = [
            'jobId'       => $request->input('jobId', 'cloud-' . time()),
            'type'        => $request->input('type'),
            'printerName' => $request->input('printerName'),
            'htmlContent' => $request->input('htmlContent'),
            'data'        => $request->input('data'), // Used for raw ESC/POS commands
        ];

        // Broadcast over Reverb to the Agent
        event(new PrintJobDispatched($businessId, $payload));

        return response()->json(['success' => true, 'message' => 'Job dispatched to agent']);
    }

    // 5. React kicks the cash drawer
    public function openDrawer(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);
        
        $printerName = $request->input('printerName');

        event(new PrintDrawerOpenRequested($businessId, $printerName));

        return response()->json(['success' => true]);
    }

    // 6. React checks if the Agent is available/online
    public function status(Request $request)
    {
        // Note: For true real-time presence, you would check Reverb Presence Channels here.
        // For now, returning true allows the flow to proceed and fall back gracefully if needed.
        return response()->json(['is_online' => true]);
    }

    // 7. Update Agent Job Status (When agent completes or fails a job)
    public function updateJobStatus(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);
        $jobId      = $request->input('jobId');
        $status     = $request->input('status'); // e.g., 'success', 'failed'
        $error      = $request->input('error');

        // Log the result. You could also store this in the database to show users print history.
        if ($status === 'failed') {
            Log::error("Print Agent Job Failed [Business: {$businessId}] [Job: {$jobId}]: {$error}");
        } else {
            Log::info("Print Agent Job Success [Business: {$businessId}] [Job: {$jobId}]");
        }

        return response()->json(['success' => true]);
    }
}