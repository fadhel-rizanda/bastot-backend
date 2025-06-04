<?php

namespace App\Traits;


use Exception;

trait ResponseAPI
{
    public function sendSuccessResponse($message, $statusCode, $status, $data)
    {
        return response()->json([
            "status" => $status ?: "success",
            "data" => $data ?: [],
            'message' => $message,
        ],
            $statusCode ?: 200,
            [], // biar ga warning
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT // biar format penulisan rapi
        );
    }
    public function sendSuccessPaginationResponse($message, $statusCode, $status, $data)
    {
        return response()->json([
            "status" => $status ?: "success",
            "data" => $data->items(),
            'message' => $message,
            'current_page' => $data->currentPage(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'next_page_url' => $data->nextPageUrl(),
            'prev_page_url' => $data->previousPageUrl(),
            'first_page_url' => $data->url(1),
            'last_page_url' => $data->url($data->lastPage()),
        ],
            $statusCode ?: 200,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    public function sendErrorResponse($message, $statusCode, $status, $data)
    {
        return response()->json([
            'status' => $status ?: 'Something Wrong!',
            'message' => $message,
            "data" => $data ?: [],
        ],
            $statusCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    public function sendExceptionResponse($message, $statusCode, $status, Exception $e)
    {
        $exception = [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'message' => $e->getMessage(),
        ];

        return response()->json([
            'status' => $status ?: 'Something Wrong!',
            'exception' => $exception,
            'message' => $message,
        ],
            $statusCode ?: 500,
            [],
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

}

