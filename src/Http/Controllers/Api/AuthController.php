<?php

namespace Porygon\User\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Porygon\User\Services\WechatMiniAppService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public WechatMiniAppService $service;
    public function __construct(WechatMiniAppService $service)
    {
        $this->service = $service;
    }
    /**
     * 小程序登录
     */
    // #[OA\Post(
    //     path: 'api/wechat/login',
    //     operationId: "WechatPostLogin",
    //     description: "微信小程序登录",
    //     tags: ["Wechat Post Login"],
    //     parameters: [
    //         new OA\Parameter(
    //             parameter: "code",
    //             name: "code",
    //             description: "wx.login获取的code",
    //             required: true,
    //         ),
    //         new OA\Parameter(
    //             parameter: "userInfo",
    //             name: "用户信息",
    //             required: true,
    //             description: "用户信息",
    //         ),
    //     ]
    // )]
    // #[OA\Response(response: 200, description: "Success")]
    public function miniappPostLogin(Request $requset)
    {
        $code     = $requset->code;
        $userInfo = $requset->userInfo;

        $response = $this->service->code2session($code);
        $userInfo = $this->service->decryptUserInfo($response["session_key"], $userInfo);

        $openid  = $response["openid"];
        $unionid = $response["unionid"] ?? null;
        $user    = $this->service->getUser($openid, $unionid, $userInfo, "mini_app");

        $token = $user->createToken("mini_app");
        return success(["token" => $token->plainTextToken, "user" => [
            "nickName"    => $user->name,
            "avatarUrl"   => $user->profile_photo_path,
            "gender"      => 0,
            "phoneNumber" => null,
            "new_user"    => (bool)$user->new_user,
        ]]);
    }


    /**
     * 获取当前用户信息
     */
    // #[OA\Post(
    //     path: 'api/wechat/info',
    //     operationId: "WechatUserInfo",
    //     description: "获取用户信息",
    //     tags: ["Wechat User Info"],
    // )]
    // #[OA\Response(response: 200, description: "Success")]
    public function getUserInfo(Request $request)
    {
        $user = $request->user();
        // $token = $user->currentAccessToken();
        // $oauth = $user->wechat_auths()->where("type", $token->name)->first();

        // $info = $oauth->only("nickname", "avatar");
        $info = [
            "id"          => $user->id,
            "nickName"    => $user->name,
            "avatarUrl"   => $user->profile_photo_path,
            "gender"      => 0,
            "phoneNumber" => null
        ];
        return success($info);
    }
}
