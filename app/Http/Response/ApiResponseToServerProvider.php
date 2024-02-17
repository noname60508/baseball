<?php

namespace App\Http\Response;

class ApiResponseToServerProvider
{
    public static function apiResponse($code = 0, $data = [])
    {
        return [
            'code'    => (string) $code,
            'message' => trans('errorCode.' . $code),
            'data'    => $data,
        ];
    }
}
