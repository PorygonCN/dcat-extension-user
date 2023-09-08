<?php

use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "wechat"], function () {
    Route::any("miniapp/notify", [WechatController::class, "notify"])->name("wechat.miniapp.notify");
    Route::post("login", [AuthController::class, "postLogin"]);
    Route::group(["middleware" => "auth:sanctum"], function () {
        Route::get("info", [AuthController::class, "getUserInfo"]);
    });
});
