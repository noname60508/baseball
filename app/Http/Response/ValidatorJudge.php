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
    protected function returnFailureMessages($validator)
    {
        return $this->apiResponse(2, false, $validator->errors());
    }
}
