<?php

namespace Porygon\User\Models;

use Porygon\Base\Models\User as BaseUser;
use Porygon\User\Traits\HasWechatAuth;

class User extends BaseUser
{
    use HasWechatAuth;
}
