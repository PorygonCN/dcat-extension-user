<?php

namespace Porygon\User\Models;

use HasWechatAuth;
use Porygon\Base\Models\User as BaseUser;

class User extends BaseUser
{
    use HasWechatAuth;
}
