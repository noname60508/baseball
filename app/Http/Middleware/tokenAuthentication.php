<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Validator;
use App\Http\Response\ApiResponse;
use App\Http\Response\ValidatorJudge;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\BaseMiddleware;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class tokenAuthentication extends BaseMiddleware
{
    use ValidatorJudge;
    use ApiResponse;
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
            $apiResponse = $this->ApiResponse(100, false, 'token必須輸入');
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
                    $apiResponse = $this->ApiResponse(311, false, 'Token過期');
                    // return response($apiResponse, 200);
                    return response()->apiResponse(311, $apiResponse);
                }
            }
        } catch (\Exception $e) {
            if ($e->getMessage() == 'The token has been blacklisted') {
                $apiResponse = $this->ApiResponse(100, false, 'token已過期或被登出');
            } else {
                $apiResponse = $this->ApiResponse(100, false, '請檢察token是否正確');
                // $apiResponse = $this->ApiResponse(100, false, $e->getMessage());
            }
            // return response($apiResponse, 200);
            return response()->apiResponse(311, $apiResponse);
        }
    }
}
