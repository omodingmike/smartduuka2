<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function response($success = false, $message = '', $data = null): JsonResponse
    {
        return response()->json([
            'status'  => $success ? 1 : 0,
            'message' => $success ? 'success' : $message,
            'data'    => $data
        ], $success ? 200 : 500);
    }
}
