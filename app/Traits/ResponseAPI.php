<?php

namespace App\Traits;


use Exception;

trait ResponseAPI
{
    public function sendSucccessResponse($data,$statusCode, $message, $status){
        return response()->json([
            "status" => $status ?: "success",
            "data" => $data ?: [],
            'message' => $message,
        ],
            $statusCode ?: 200,
            [], // biar ga warning
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT // biar format penulisan rapi
        );
    }

    public function sendErrorResponse($message, $statusCode, $status, $data)
    {
        return response()->json([
            'status'  => $status ?: 'Something Wrong!',
            'message' => $message,
            "data" => $data ?: [],
        ],
            $statusCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }

    public function sendExceptionResponse($message, $statusCode, $status, Exception $e){
        $datas = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ];

        return response()->json([
            'status' => $status ?: 'Something Wrong!',
            'data' => $datas,
            'message' => $message,
        ],
            $statusCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        );
    }

}

