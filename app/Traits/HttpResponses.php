<?php

namespace App\Traits;

trait HttpResponses {

    public function success($data, $message = null, int $code = 200) 
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function error($data, $message = null, int $code = 401) 
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    public function restricted($data, $message = null, int $code = 401) 
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

}





