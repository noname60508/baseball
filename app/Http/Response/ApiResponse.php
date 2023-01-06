<?php

namespace App\Http\Response;

use Lang;

trait ApiResponse
{
    protected function apiResponse($code = 0, $status = false, $data = [])
    {
        return [
            'code'    => (string) $code,
            'status'  => $status ? 'success' : 'error',
            'message' => trans('errorCode.' . $code),
            'data'    => $data,
        ];
    }
    protected function apiResponseCode($code = 0, $data = [])
    {
        // 尾數為1代表成功
        return $this->apiResponse($code, $code%10===1, $data);
    }
}
