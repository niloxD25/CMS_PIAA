<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = "Opération réussie", $code = 200)
    {
        $response = [
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ];

        if (!is_null($data)) {
            if (is_array($data) || $data instanceof \Countable) {
                $response['count'] = count($data);
            } elseif (is_object($data)) {
                $response['count'] = 1;
            } else {
                $response['count'] = null;
            }
        } else {
            $response['count'] = 0;
        }

        return response()->json($response, $code);
    }

    public static function error($message = "Une erreur est survenue", $errors = null, $code = 400)
    {
        $response = [
            'status'  => 'error',
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
