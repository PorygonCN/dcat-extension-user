<?php

namespace Porygon\User;

use Dcat\Admin\Extend\ServiceProvider as BaseServiceProvider;
use Dcat\Admin\Admin;

class ServiceProvider extends BaseServiceProvider
{
    protected $js = ["js/pinyin-pro.min.js"];
    protected $css = [];

    // 定义菜单
    protected $menu = [
        [
            'title' => 'User Module',
            'uri'   => '',
            'icon'  => '',               // 图标可以留空
        ],
        [
            "parent" => "User Module",
            'title'  => 'User Info',
            'uri'    => 'user/users',
            'icon'   => '',              // 图标可以留空
        ],
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        #
    }

    public function init()
    {
        parent::init();

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->registerRoutes(__DIR__ . "/Admin/routes.php");
        $this->loadTranslationsFrom(__DIR__ . "/../resources/lang", "p-user");

        $this->publishes([__DIR__ . "/../resources/lang" => app()->langPath()], "porygon-user-lang");

        require __DIR__ . "/bootstrap.php";
    }

    public function settingForm()
    {
        return new Setting($this);
    }
}
