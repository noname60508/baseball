<?php

namespace App\Http\Response;

class ApiResponseToServerProvider
{
    public static function apiResponse($code = 0, $data = [])
    {
        return [
            'code'    => (string) $code,
            // 'status'  => ($code % 10 === 1) ? true : false,
            'message' => trans('errorCode.' . $code),
            'data'    => $data,
        ];
    }
}
