<?php

namespace Porygon\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Porygon\Base\Models\Model;
use Porygon\Base\Models\User;

class WechatAuth extends Model
{
    protected $table = "user_wechat_auths";
    protected $with  = ["user"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
