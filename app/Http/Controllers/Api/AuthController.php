<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 用户登录并签发访问令牌。
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $username = (string) $validated['username'];
        $password = (string) $validated['password'];
        $user = $this->authService->attemptLogin($username, $password);

        // 验证用户是否存在以及密码是否正确
        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => '用户名或密码错误',
            ], 401);
        }

        // 基于 Passport 生成访问令牌，供后续 auth:api 接口鉴权。
        $tokenResult = $user->createToken('Personal Access Token');

        return response()->json([
            'code' => 200,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'user_type' => $user->user_type,
            ],
        ]);
    }

    /**
     * 当前登录用户退出，撤销当前访问令牌。
     */
    public function logout(Request $request)
    {
        // 获取当前用户的访问令牌
        $token = $request->user()->token();

        // 撤销令牌
        $token->revoke();

        return response()->json([
            'code' => 200,
            'message' => '成功退出登录',
        ], 200);
    }
}