<?php

    namespace App\Traits;

    use Illuminate\Http\JsonResponse;

    trait ApiResponse
    {
        public function response(bool $success = FALSE , string $message = '' , mixed $data = NULL , int $status = 200) : JsonResponse
        {
            return response()->json( [
                'status'  => $success ? 1 : 0 ,
                'message' => $success ? 'success' : $message ,
                'data'    => $data
            ] , $status );
        }

        public function success(mixed $data = NULL , int $status = 200) : JsonResponse
        {
            return response()->json( [
                'status' => 1 ,
                'data'   => $data
            ] , $status );
        }

        public function error(string $message = '' , int $status = 500) : JsonResponse
        {
            return response()->json( [
                'status'  => 0 ,
                'message' => $message ,
            ] , $status );
        }
    }
