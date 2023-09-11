<?php

namespace Porygon\User\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WechatOauth;
use Porygon\User\Services\WechatMiniAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

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

        $openid  = $response["openid"];
        $unionid = $response["unionid"];
        $user    = $this->service->getUser($openid, $unionid, $userInfo, "mini_app");

        $token = $user->createToken("mini_app");
        return ["token" => $token->plainTextToken];
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
            "nickName"    => $user->name,
            "avatarUrl"   => $user->profile_photo_path,
            "gender"      => 0,
            "phoneNumber" => null
        ];
        return $info;
    }
}
