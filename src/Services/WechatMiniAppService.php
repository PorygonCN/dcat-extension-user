<?php

namespace Porygon\User\Services;

use Porygon\User\Models\WechatAuth;
use Porygon\User\Models\User;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\MiniApp\Application;
use Exception;
use Illuminate\Support\Facades\DB;

class WechatMiniAppService
{

    public Application $app;
    public function __construct()
    {
        $this->app = app("easywechat.mini_app");
    }


    /**
     * 通过code换取openid sessionKey unionid
     */
    public function code2session($code): Response
    {
        $api      = $this->app->getClient();
        $account  = $this->app->getAccount();
        $response = $api->get("/sns/jscode2session", [
            "appid"      => $account->getAppId(),
            "secret"     => $account->getSecret(),
            "js_code"    => $code,
            "grant_type" => "authorization_code",
        ]);
        return $response;
    }

    /**
     * 通过openid和unionid获取User 没有则根据userInfo创建
     */
    public function getUser($openid, $unionid, $userInfo, $type): User
    {
        DB::beginTransaction();
        try {
            // 获取oauth 没有则创建
            $oauth = WechatAuth::query()->firstOrCreate(["type" => $type, "openid" => $openid], [
                "unionid"  => $unionid,
                "avatar"   => $userInfo["avatarUrl"],
                "nickname" => $userInfo["nickName"]
            ]);
            // 可能是创建的 所以更新一下
            $oauth->update([
                "openid"   => $openid,
                "unionid"  => $unionid,
                "avatar"   => $userInfo["avatarUrl"],
                "nickname" => $userInfo["nickName"]
            ]);
            $user = $oauth->user;

            // 没有user_id  说明是个新的oauth
            if (!$oauth->user_id) {
                // 判断当前有没有unionid
                if ($unionid) {
                    // 有则获取其他unionid相同的oauth
                    $unionOauth = WechatAuth::query()->where("unionid", $unionid)->where("id", "!=", $oauth->id)->first();
                    // 获取到了
                    if ($unionOauth) {
                        // 设置当前用户
                        $user = $unionOauth->user;
                    }
                }
                // 还是没找到用户
                if (!$user) {
                    // 则创建
                    $user = User::create([
                        "name"               => $userInfo["nickName"],
                        "nickname"           => $userInfo["nickName"],
                    ]);
                    try { // 尝试保存头像 可能没有
                        $user->profile_photo_path = $userInfo["avatarUrl"];
                        $user->save();
                    } catch (Exception $e) {
                    }
                }
                // 关联当前oauth
                $user->wechat_auths()->save($oauth);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $user;
    }
}
