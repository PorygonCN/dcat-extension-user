<?php

namespace Porygon\User\Http\Controllers\Api;

use App\Events\Cash\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\Market\OrderResource;
use App\Models\Cash\Order;
use App\Models\Cash\OrderItem;
use App\Models\Market\Sku;
use App\Models\User;
use App\Services\CashService;
use EasyWeChat\MiniApp\Application;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WechatController extends Controller
{
    public function notify(Request $request)
    {
        /** @var Application */
        $app = app("easywechat.mini_app");
        $server = $app->getServer();


        $response = $server->serve();

        // 将响应输出
        return $response;
    }
}
