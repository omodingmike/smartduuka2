<?php

    namespace App\Http\Controllers;

    use App\Helpers\printing\DrawerKickDispatched;
    use App\Helpers\printing\PrintJobDispatched;
    use App\Helpers\printing\PrintJobPayload;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;

    class PrintAgentController extends Controller
    {
        public function sendJob(Request $request) : JsonResponse
        {
            $validated = $request->validate( [
                'printer_name' => 'required|string' ,
                'type'         => 'required|in:html,raw' ,
                'html_content' => 'required_if:type,html|string' ,
                'raw_data'     => 'required_if:type,raw|string' ,
            ] );

            $payload = $validated[ 'type' ] === 'html'
                ? PrintJobPayload::createHtml( $validated[ 'printer_name' ] , $validated[ 'html_content' ] )
                : PrintJobPayload::createRaw( $validated[ 'printer_name' ] , $validated[ 'raw_data' ] );

            $tenant     = tenant();
            $identifier = $tenant->custom_domain ?? $tenant->business_id;

            PrintJobDispatched::dispatch( $identifier , $payload->toArray() );

            return response()->json( [
                'status'  => 'success' ,
                'message' => 'Job dispatched to agent via Reverb' ,
                'job_id'  => $payload->jobId
            ] );
        }

        public function kickDrawer(Request $request) : JsonResponse
        {
            $validated = $request->validate( [
                'printer_name' => 'required|string' ,
            ] );

            $tenant     = tenant();
            $identifier = $tenant->custom_domain ?? $tenant->business_id;

            DrawerKickDispatched::dispatch( $identifier , $validated[ 'printer_name' ] );

            return response()->json( [
                'status'  => 'success' ,
                'message' => 'Drawer kick command dispatched via Reverb.'
            ] );
        }

        /**
         * Webhook endpoint hit by the Electron Agent to update job status.
         */
        public function updateStatus(Request $request) : JsonResponse
        {
            $validated = $request->validate( [
                'jobId'          => 'required|string' ,
                'identifier'     => 'required|string' ,
                'connectionType' => 'required|string' ,
                'status'         => 'required|in:success,failed' ,
                'error'          => 'nullable|string' ,
                'printerName'    => 'nullable|string' ,
                'timestamp'      => 'required|date'
            ] );

            // TODO: Find the job in your `print_jobs` table and update its status
            // PrintJob::where('job_id', $validated['jobId'])->update([
            //     'status' => $validated['status'],
            //     'error_message' => $validated['error']
            // ]);

            // For now, log the status update for your audit trails
            Log::info( "Print Job {$validated['jobId']} updated to {$validated['status']}" , $validated );

            return response()->json( [
                'status'  => 'success' ,
                'message' => 'Job status updated successfully.'
            ] );
        }
    }
