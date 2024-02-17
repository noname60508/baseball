<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Validator;
use App\Http\Response\ApiResponseToServerProvider;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class tokenAuthentication extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 檢查是否有token
        if (empty($request->bearerToken())) {
            $apiResponse = ApiResponseToServerProvider::apiResponse(100, 'token必須輸入');
            return response($apiResponse, 200);
        }
        try {
            try {
                // 成功解析token
                if ($this->auth->parseToken()->authenticate()) {
                    // 將token放入header
                    return $next($request)->header('Authorization', $request->bearerToken());
                }
                throw new UnauthorizedHttpException('jwt-auth', '未登入');
            } catch (TokenExpiredException $exception) {
                try {
                    // 如果token時效過期，但可刷新時間沒過期，刷新token
                    $token = auth()->refresh();
                    // 將刷新後的token放入header
                    return $next($request)->header('Authorization', $token);
                } catch (JWTException $e) {
                    // 如果token時效過期，可刷新時間過期
                    $apiResponse = ApiResponseToServerProvider::apiResponse(311, 'Token過期');
                    return response($apiResponse, 200);
                }
            }
        } catch (\Exception $e) {
            if ($e->getMessage() == 'The token has been blacklisted') {
                $apiResponse = ApiResponseToServerProvider::apiResponse(100, 'token已過期或被登出');
            } else {
                $apiResponse = ApiResponseToServerProvider::apiResponse(100, '請檢察token是否正確');
            }
            // return response($e->getMessage(), 200);
            return response($apiResponse, 200);
        }
    }
}
