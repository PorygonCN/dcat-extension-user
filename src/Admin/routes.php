<?php

use Illuminate\Support\Facades\Route;
use Porygon\User\Admin\Controllers\UserController;

Route::group(["prefix" => "user", "as" => "user."], function () {
    Route::resources([
        "users"       => UserController::class,          // 用户管理
    ]);
    // 获取用户导入模板
    Route::get("users/import/template", [UserController::class, "getImportTemplate"])->name("users.import.template");
});

Route::group(["prefix" => "api", "as" => "api."], function () {
    Route::group(["prefix" => "user", "as" => "user."], function () {
        // 获取新工号
        Route::get("getleastno", [UserController::class, "getLeastNo"])->name("getleastno");
    });
});
