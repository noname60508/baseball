<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Response;
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
        Response::macro('ApiResponse', function ($code, $date) {
            return ApiResponseToServerProvider::apiResponse($code, $date);
        });
    }
}
