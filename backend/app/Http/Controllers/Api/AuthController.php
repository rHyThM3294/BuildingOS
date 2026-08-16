<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * 純 Bearer token 的 Sanctum 驗證，不用 session/cookie（前後端不同網域，
 * Sanctum 的 SPA cookie 模式要同網域或另外設定 stateful domains，用
 * Personal Access Token 反而單純：登入換一組 token，之後每支 API 都帶
 * Authorization: Bearer {token}，跟前端 axios 攔截器現有的邏輯直接吻合。
 */
class AuthController extends Controller
{
    #[OA\Post(
        path: '/login',
        operationId: 'login',
        summary: '登入並取得 Bearer token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'demo@buildingos.test'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'buildingos-demo'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: '登入成功', content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                ],
            )),
            new OA\Response(response: 422, description: '帳號或密碼錯誤'),
        ],
    )]
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => '帳號或密碼錯誤']);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    #[OA\Post(
        path: '/logout',
        operationId: 'logout',
        summary: '登出（撤銷目前這組 token）',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 204, description: '已登出'),
            new OA\Response(response: 401, description: '未登入或 token 已失效'),
        ],
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/user',
        operationId: 'me',
        summary: '取得目前登入的使用者資訊（驗證 token 是否有效用）',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: '成功', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 401, description: '未登入或 token 已失效'),
        ],
    )]
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
