<?php


use Illuminate\Support\Facades\Route;
use Porygon\Meeting\Admin\Controllers\BuildingController;
use Porygon\Meeting\Admin\Controllers\RoomController;
use Porygon\Meeting\Http\Controllers\Web\RoomController as WebRoomController;

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'middleware' => config('admin.route.middleware'),
    "as" => "dcat.admin."
], function () {
    /**
     * 会议室管理
     */
    Route::group(["prefix" => "meeting",  "as" => "meeting."], function () {
        Route::resources([
            "buildings" => BuildingController::class,
            "rooms"    => RoomController::class,
        ]);
    });
});

Route::group(["prefix" => "meeting", "as" => "meeting."], function () {
    Route::resource("rooms", WebRoomController::class)->only("index", "show");
});
