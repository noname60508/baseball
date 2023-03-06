<?php

namespace App\Http\Response;

trait ValidatorJudge
{
    /**
     * 若驗證不通過回傳錯誤訊息
     *
     * @param [type] $validator
     * @return Array
     */
    protected function failureMessages($validator)
    {
        return response()->apiResponse(100, $validator->errors());
        // return $this->apiResponse(2, false, $validator->errors());
    }
}
