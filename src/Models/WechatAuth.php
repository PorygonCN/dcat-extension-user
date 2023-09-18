<?php

namespace Porygon\User\Models;

use App\Models\User;
use Porygon\Base\Models\Model;

class WechatAuth extends Model
{
    protected $table = "user_wechat_auths";
    protected $with  = ["user"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
