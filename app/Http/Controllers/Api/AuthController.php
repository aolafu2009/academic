<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * 用户登录并签发访问令牌。
     */
    public function login(Request $request)
    {
        // 验证请求参数
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        // 统一使用 username 字段登录，便于和管理后台账号体系保持一致。
        $user = User::where('username', $request->username)->first();

        // 验证用户是否存在以及密码是否正确
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([   
                'code' => '401',             
                'message' => '用户名或密码错误'
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
                'user_type' => $user->user_type
            ]
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
            'message' => '成功退出登录'
        ], 200);
    }
}