<?php

use Illuminate\Support\Facades\Route;
use Porygon\User\Http\Controllers\Api\AuthController;
use Porygon\User\Http\Controllers\Api\WechatController;

Route::middleware('api')
    ->prefix('api')
    ->group(function () {
        Route::group(["prefix" => "wechat"], function () {
            Route::any("miniapp/notify", [WechatController::class, "notify"])->name("wechat.miniapp.notify");
            Route::post("miniapp/login", [AuthController::class, "miniappPostLogin"]);
            Route::group(["middleware" => "auth:sanctum"], function () {
                Route::get("info", [AuthController::class, "getUserInfo"]);
            });
        });
    });
