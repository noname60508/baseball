<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use App\Http\Response\ApiResponseToServerProvider;

class responseServerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // api回傳資料
        Response::macro('apiResponse', function ($code, $date, $token = null) {
            if (empty($token)) {
                return ApiResponseToServerProvider::apiResponse($code, $date);
            } else {
                return Response::json(ApiResponseToServerProvider::apiResponse($code, $date), 200)
                    ->header('Authorization', $token);
            }
        });

        // 驗證失敗回傳
        Response::macro('returnFailureMessages', function ($validator) {
            return ApiResponseToServerProvider::apiResponse(100, $validator->errors());
        });
    }
}
