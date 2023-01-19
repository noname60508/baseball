<?php

namespace App\Http\Controllers\Account;

use Exception;
use App\Models\users;

use App\Http\Controllers\Controller;
use App\Http\Response\ApiResponse;
use App\Http\Response\ValidatorJudge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    use ValidatorJudge;
    use ApiResponse;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // 套入middleware，except(除外)login, register
        $this->middleware('tokenAuthentication', ['except' => ['login', 'register']]);
    }

    /**
     * 登入
     *
     */
    public function login(Request $request)
    {
        //驗證
        $validator = Validator::make(
            [
                'accountOrEmail' => $request->accountOrEmail,
                'password' => $request->password,
            ],
            [
                'accountOrEmail'   => 'required|string',
                'password' => 'required|string',
            ],
            [
                'accountOrEmail.required'  => '【帳號】必填',
                'accountOrEmail.string'    => '【帳號】須為字串',
                'password.required' => '【密碼】必填',
                'password.string'   => '【密碼】須為字串',
            ]
        );
        if ($validator->fails()) {
            return $this->returnFailureMessages($validator);
        }

        try {
            $user = users::where(function ($query) use ($request) {
                $query->where('account', $request->accountOrEmail);
                $query->orWhere('email', $request->accountOrEmail);
            })
                ->select('id', 'name', 'email')
                ->first();

            if (empty($user)) {
                return $this->apiResponse(302, true, '登入失敗，請檢查輸入【帳號、密碼】是否正確。');
            }
            // return $user;

            $login['email'] = $user->email;
            $login['password'] = $request->password;
            if (!$token = auth::attempt($login)) {
                return $this->apiResponse(302, true, '登入失敗，請檢查輸入【帳號、密碼】是否正確。');
            }

            $user->update(['dt_login' => $this->nowTime]);

            // return response()->header('Authorization', $token)->ApiResponse(101, $request->all());
            $response = $this->apiResponse(301, true, $user);
            return response($response, 200)->header('Authorization', $token);
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 登出
     *
     */
    public function logout()
    {
        try {
            // 取得token使用者資訊
            $user = auth()->user();

            users::where('id', $user->id)->update(['dt_logout' => $this->nowTime]);

            auth()->logout();
            return $this->apiResponse(303, true, '登出成功');
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 新增帳號
     *
     */
    public function register(Request $request)
    {
        //驗證
        $validator = Validator::make(
            [
                'account'  => $request->account,
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
            ],
            [
                'account'   => 'required|string',
                'name'      => 'required|string',
                'email'     => 'required|email',
                'password'  => 'required|string',
            ],
            [
                'account.required'  => '【帳號】必填',
                'account.string'    => '【帳號】須為字串',
                'name.required'     => '【使用者名稱】必填',
                'name.string'       => '【使用者名稱】須為字串',
                'email.required'    => '【email】必填',
                'email.email'       => '【email】須為email格式',
                'password.required' => '【密碼】必填',
                'password.string'   => '【密碼】須為字串',
            ]
        );
        if ($validator->fails()) {
            return $this->returnFailureMessages($validator);
        }

        try {
            $userData = [
                'account'  => $request->account,
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => bcrypt($request->password),
            ];

            $user = users::create($userData);

            return $this->apiResponse(309, true, $user);
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 刪除帳號
     *
     */
    public function deleteUser(Request $request)
    {
        //驗證
        $validator = Validator::make(
            [
                'id' => $request->id,
            ],
            [
                'id' => 'required|integer',
            ],
            [
                'id.required'     => '【使用者ID】必填',
                'id.integer'      => '【使用者ID】須為整數',
            ]
        );
        if ($validator->fails()) {
            return $this->returnFailureMessages($validator);
        }

        try {
            if ($request->id == auth()->user()->id)
                return $this->apiResponse(500, false, '無法刪除自身帳號');

            users::where('id', $request->id)->delete();

            return $this->apiResponse(501, true, '刪除成功');
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 修改帳號
     *
     */
    public function updateUser(Request $request)
    {
        //驗證
        $validator = Validator::make(
            [
                'id'       => $request->id,
                'account'  => $request->account,
                'name'     => $request->name,
                'password' => $request->password,
                'c_id'     => $request->c_id,
                'email'    => $request->email,
            ],
            [
                'id'       => 'required|integer',
                'account'  => 'nullable|string',
                'name'     => 'nullable|string',
                'password' => 'nullable|string',
                'c_id'     => 'nullable|integer',
                'email'    => 'nullable|email',
            ],
            [
                'id.required'     => '【使用者ID】須為字串',
                'id.integer'      => '【使用者ID】須為字串',
                'account.string'  => '【帳號】須為字串',
                'name.string'     => '【使用者名稱】須為字串',
                'password.string' => '【密碼】須為字串',
                'c_id.integer'    => '【所屬廠商流水號】須為整數',
                'email.email'     => '【email】須為email格式',
            ]
        );

        if ($validator->fails()) {
            return $this->returnFailureMessages($validator);
        }

        try {
            $updateRequest = collect([]);

            foreach ($request->only(['name', 'account', 'c_id', 'password', 'email']) as $key => $value) {
                if (!empty($value)) {
                    // 如果修改密碼先加密
                    if ($key == 'password')
                        $value = bcrypt($value);

                    $updateRequest = $updateRequest->merge([$key => $value]);
                }
            }

            $update = users::where('id', $request->id)->first();
            if (empty($update)) {
                return $this->apiResponse(101, true, '無此使用者');
            }
            $update->update($updateRequest->toArray());

            return $this->apiResponse(201, true, $update);
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 使用者清單
     *
     */
    public function userList(Request $request)
    {
        //驗證
        $validator = Validator::make(
            [
                'name' => $request->name,
                'account' => $request->account,
                // 'c_id' => $request->c_id,
                'page' => $request->page,
            ],
            [
                'name' => 'nullable|string',
                'account' => 'nullable|string',
                // 'c_id' => 'nullable|integer',
                'page' => 'nullable|integer',
            ],
            [
                'name.string'    => '【使用者名稱】須為字串',
                'account.string' => '【使用者帳號】須為字串',
                // 'c_id.integer'   => '【公司ID】須為整數',
                'page.integer'   => '【頁數】須為整數',
            ]
        );
        if ($validator->fails()) {
            return $this->returnFailureMessages($validator);
        }

        try {
            $users = users::select('id', 'account', 'name', 'authority');

            foreach ($request->only(['name', 'account']) as $key => $value) {
                if (!empty($value)) {
                    $users = $users->where($key, $value);
                }
            }

            if ($request->page > 0) {
                $paginate = $users->paginate(10);
                $output = [
                    'page' => $paginate->lastPage(),
                    'output' => $paginate->items(),
                ];
            } else {
                $output = $users->get();
            }

            return $this->apiResponse(101, true, $output);
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }

    /**
     * 取得token資訊
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        try {
            return response()->json(auth()->user());
        } catch (\Throwable $e) {
            return $this->apiResponse(100, false, $e->getMessage());
        }
    }
}
