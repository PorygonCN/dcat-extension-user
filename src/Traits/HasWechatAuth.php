<?php

namespace Porygon\User\Traits;

use Porygon\User\Models\WechatAuth;

trait HasWechatAuth
{
    public function wechat_auths()
    {
        return $this->hasMany(WechatAuth::class);
    }
}
